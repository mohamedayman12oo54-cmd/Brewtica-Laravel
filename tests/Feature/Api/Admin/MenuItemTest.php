<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Customer;
use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\MenuItemSizePrice;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuItemTest extends TestCase
{
    // ======= Helpers =======
    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createSubSubCategory(): SubSubCategory
    {
        $main = Main_Category::factory()->create();
        $sub  = SubCategory::factory()->create(['main_category_id' => $main->id]);
        return SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
    }

    // ==========================================
    // CREATE TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_create_menu_item_with_prices(): void
    {
        $admin  = $this->createAdmin();
        $subSub = $this->createSubSubCategory();

        $response = $this->actingAs($admin, 'api')
                         ->postJson('/api/admin/menu-items', [
                             'sub_sub_category_id' => $subSub->id,
                             'name'                  => 'Latte',
                             'description'           => 'Creamy coffee',
                             'prices'                => [
                                 ['size' => 'small',  'price' => 25.00],
                                 ['size' => 'medium', 'price' => 35.00],
                                 ['size' => 'large',  'price' => 45.00],
                             ],
                         ]);

        $response->assertStatus(201);

        $item = MenuItem::where('name', 'Latte')->first();
        $this->assertNotNull($item);
        $this->assertEquals(3, $item->sizePrices()->count());
        $this->assertDatabaseHas('menu_item_size_prices', [
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'price'        => 35.00,
        ]);
    }

    /** @test */
    public function test_admin_can_create_menu_item_with_image(): void
    {
        Storage::fake('public');
        $admin  = $this->createAdmin();
        $subSub = $this->createSubSubCategory();

        $response = $this->actingAs($admin, 'api')
                         ->postJson('/api/admin/menu-items', [
                             'sub_sub_category_id' => $subSub->id,
                             'name'                  => 'Cappuccino',
                             'image'                 => UploadedFile::fake()->image('cap.jpg'),
                             'prices'                => [['size' => 'medium', 'price' => 30.00]],
                         ]);

        $response->assertStatus(201);

        $item = MenuItem::where('name', 'Cappuccino')->first();
        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists($item->image);
    }

    /** @test */
    public function test_creating_menu_item_requires_at_least_one_price(): void
    {
        $admin  = $this->createAdmin();
        $subSub = $this->createSubSubCategory();

        $this->actingAs($admin, 'api')
             ->postJson('/api/admin/menu-items', [
                 'sub_sub_category_id' => $subSub->id,
                 'name'                  => 'Mocha',
                 'prices'                => [],
             ])
             ->assertStatus(422);
    }

    // ==========================================
    // UPDATE TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_update_menu_item_prices(): void
    {
        $admin  = $this->createAdmin();
        $subSub = $this->createSubSubCategory();
        $item   = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);
        MenuItemSizePrice::factory()->create(['menu_item_id' => $item->id, 'size' => 'medium', 'price' => 30.00]);

        $this->actingAs($admin, 'api')
             ->patchJson("/api/admin/menu-items/{$item->id}", [
                 'prices' => [['size' => 'medium', 'price' => 40.00]],
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('menu_item_size_prices', [
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'price'        => 40.00,
        ]);
    }

    /** @test */
    public function test_admin_can_update_menu_item_name(): void
    {
        $admin  = $this->createAdmin();
        $subSub = $this->createSubSubCategory();
        $item   = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id, 'name' => 'Old']);

        $this->actingAs($admin, 'api')
             ->patchJson("/api/admin/menu-items/{$item->id}", ['name' => 'New'])
             ->assertStatus(200);

        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'name' => 'New']);
    }

    // ==========================================
    // DELETE TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_delete_menu_item_without_orders(): void
    {
        $admin  = $this->createAdmin();
        $subSub = $this->createSubSubCategory();
        $item   = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/menu-items/{$item->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_menu_item_with_order_history(): void
    {
        $admin    = $this->createAdmin();
        $subSub   = $this->createSubSubCategory();
        $item     = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);
        $customer = User::factory()->create();
        Customer::factory()->create(['user_id' => $customer->id]);
        $order = Order::factory()->create(['customer_id' => $customer->customer->id]);
        OrderDetail::factory()->create([
            'order_id'     => $order->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 1,
            'price'        => 30.00,
        ]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/menu-items/{$item->id}")
             ->assertStatus(422);

        $this->assertDatabaseHas('menu_items', ['id' => $item->id]);
    }

    /** @test */
    public function test_non_admin_cannot_access_menu_item_routes(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer, 'api')
             ->getJson('/api/admin/menu-items')
             ->assertStatus(403);
    }
}
