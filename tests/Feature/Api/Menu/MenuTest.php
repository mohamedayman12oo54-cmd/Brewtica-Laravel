<?php

namespace Tests\Feature\Api\Menu;

use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\MenuItemSizePrice;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use GuzzleHttp\Promise\Create;
use Tests\TestCase;

class MenuTest extends TestCase
{
    // ======= Helper: بنبني Menu Structure كاملة =======
    private function createMenuStructure(): MenuItem
    {
        $main = Main_Category::factory()->create();
        $sub = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        $item = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        MenuItemSizePrice::factory()->create([
            'menu_item_id' => $item->id,
            'size' => 'small',
            'price' => '25.00',
        ]);

        return $item;
    }

    // ==========================================
    // CATEGORIES TESTS
    // ==========================================

    /** @test */
    public function test_can_get_all_categories(): void
    {
        Main_Category::factory()->count(3)->create();

        $response = $this->getJson('/api/menu/categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => ['id', 'name', 'description', 'sub_categories']
                    ]
                 ]);
    }

    /** @test */
    public function test_returns_empty_array_when_no_categories(): void
    {
        $response = $this->getJson('/api/menu/categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'status',
                    'data' => [],
                 ]);
    }

    // ==========================================
    // ITEMS TESTS
    // ==========================================

    /** @test */
    public function test_can_get_all_items(): void
    {
        $this->createMenuStructure();

        $response = $this->getJson('/api/menu/items');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => ['id', 'name', 'description','image', 'prices']
                    ]
                 ]);
    }

    /** @test */
    public function test_can_filter_items_by_category(): void 
    {
        $item = $this->createMenuStructure();
        $othItem = $this->createMenuStructure();

        $response = $this->getJson("/api/menu/items?category_id={$item->sub_sub_category_id}");

        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($item->id));
        $this->assertFalse($ids->contains($othItem->id));

    }

    /** @test */
    public function test_can_search_items_by_name(): void
    {
        $item = $this->createMenuStructure();

        MenuItem::factory()->create([
            'name' => 'Competely Different',
            'sub_sub_category_id' => $item->sub_sub_category_id,
        ]);

        $response = $this->getJson("api/menu/items?q={$item->name}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains($item->name));
        $this->assertFalse($names->contains('Completely Different'));
    }

    /** @test */
    public function test_items_without_prices_are_excluded(): void
    {
        $main = Main_Category::factory()->create();
        $sub = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);

        $item = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $response = $this->getJson('api/menu/items');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($item->id));
    }

    // ==========================================
    // SINGLE ITEM TESTS
    // ==========================================

    /** @test */
    public function test_can_get_single_item_with_full_details(): void
    {
        $item = $this->createMenuStructure();

        $response = $this->getJson("api/menu/items/{$item->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id', 'name', 'description',
                        'ingredients', 'image',
                        'category' => ['main', 'sub', 'sub_sub'],
                        'prices',
                    ]
                 ]);
    }

    /** @test */
    public function test_returns_404_for_nonexistent_item(): void
    {
        $response = $this->getJson('api/menu/items/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_menu_endpoints_are_public_no_auth_required(): void
    {
        $response = $this->getJson('api/menu/categories');

        $response->assertStatus(200);
    }

}
