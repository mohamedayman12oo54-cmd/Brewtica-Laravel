<?php

use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\MenuItemSizePrice;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use App\Models\User;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    // ======= Helpers =======
    private function createCustomer(): User
    {
        $user = User::factory()->create();
        Customer::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function createMenuItem(): MenuItem
    {
        $main = Main_Category::factory()->create();
        $sub = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        $item = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        MenuItemSizePrice::factory()->create([
            'menu_item_id' => $item->id,
            'size' => 'medium',
            'price' => 35.00,
        ]);

        return $item;
    }

    // ==========================================
    // INDEX TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_view_their_favorites(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        Favorite::factory()->create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
        ]);

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/favorites');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'id',
                             'menu_item' => ['id', 'name', 'image', 'prices'],
                             'created_at',
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function test_customer_sees_only_their_own_favorites(): void
    {
        $user1 = $this->createCustomer();
        $user2 = $this->createCustomer();
        $item  = $this->createMenuItem();

        Favorite::factory()->create([
            'user_id'      => $user2->id,
            'menu_item_id' => $item->id,
        ]);

        $response = $this->actingAs($user1, 'api')
                         ->getJson('/api/favorites');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    /** @test */
    public function test_guest_cannot_view_favorites(): void
    {
        $this->getJson('/api/favorites')
             ->assertStatus(401);
    }

    // ==========================================
    // TOGGLE TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_add_item_to_favorites(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        $response = $this->actingAs($user, 'api')
                         ->postJson("/api/favorites/{$item->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'action' => 'added',
                 ]);

        $this->assertDatabaseHas('favorites', [
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
        ]);
    }

    /** @test */
    public function test_customer_can_remove_item_from_favorites_via_toggle(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        // ضيفه الأول
        Favorite::factory()->create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
        ]);

        // Toggle تاني = يشيله
        $response = $this->actingAs($user, 'api')
                         ->postJson("/api/favorites/{$item->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'action' => 'removed',
                 ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
        ]);
    }

    /** @test */
    public function test_toggle_returns_404_for_nonexistent_item(): void
    {
        $user = $this->createCustomer();

        $this->actingAs($user, 'api')
             ->postJson('/api/favorites/99999')
             ->assertStatus(404);
    }
}
