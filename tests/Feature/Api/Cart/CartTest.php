<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\MenuItemSizePrice;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use App\Models\User;
use App\OrderSize;
use Tests\TestCase;

class CartTest extends TestCase
{
    // ======= Helpers =======
    private function createCustomer(): User
    {
        $user = User::factory()->create();
        Customer::factory()->create(['user_id' => $user->id]);
        return $user;
    }

    private function createMenuItem(array $prices = []): MenuItem
    {
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        $item   = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $defaultPrices = empty($prices) ? [
            ['size' => 'small',  'price' => 25.00],
            ['size' => 'medium', 'price' => 35.00],
            ['size' => 'large',  'price' => 45.00],
        ] : $prices;

        foreach ($defaultPrices as $price) {
            MenuItemSizePrice::factory()->create([
                'menu_item_id' => $item->id,
                'size'         => $price['size'],
                'price'        => $price['price'],
            ]);
        }

        return $item;
    }

    // ==========================================
    // GET CART TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_view_their_cart(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 2,
        ]);

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'items' => [
                             '*' => ['menu_item', 'size', 'unit_price', 'quantity', 'subtotal']
                         ],
                         'total',
                         'items_count',
                     ]
                 ]);
    }

    /** @test */
    public function test_cart_calculates_totals_correctly(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium', // price = 35.00
            'quantity'     => 2,        // subtotal = 70.00
        ]);

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'total'       => 70.00,
                         'items_count' => 1,
                     ]
                 ]);
    }

    /** @test */
    public function test_customer_sees_only_their_own_cart(): void
    {
        $user1 = $this->createCustomer();
        $user2 = $this->createCustomer();
        $item  = $this->createMenuItem();

        Cart::create([
            'user_id'      => $user2->id,
            'menu_item_id' => $item->id,
            'size'         => 'small',
            'quantity'     => 1,
        ]);

        $response = $this->actingAs($user1, 'api')
                         ->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJson(['data' => ['items_count' => 0]]);
    }

    // ==========================================
    // ADD TO CART TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_add_item_to_cart(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/cart', [
                             'menu_item_id' => $item->id,
                             'size'         => 'medium',
                             'quantity'     => 1,
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('carts', [
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 1,
        ]);
    }

    /** @test */
    public function test_adding_same_item_same_size_increments_quantity(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        // ضيفه المرة الأولى
        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 1,
        ]);

        // ضيفه تاني بنفس الـ size
        $this->actingAs($user, 'api')
             ->postJson('/api/cart', [
                 'menu_item_id' => $item->id,
                 'size'         => 'medium',
                 'quantity'     => 2,
             ]);

        // المفروض الـ quantity تبقى 3 مش row جديد
        $this->assertDatabaseHas('carts', [
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 3,
        ]);

        // التأكد إنه مش عمل row جديد
        $this->assertEquals(
            1,
            Cart::where('user_id', $user->id)
                ->where('menu_item_id', $item->id)
                ->where('size', 'medium')
                ->count()
        );
    }

    /** @test */
    public function test_adding_same_item_different_size_creates_new_row(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'small',
            'quantity'     => 1,
        ]);

        $this->actingAs($user, 'api')
             ->postJson('/api/cart', [
                 'menu_item_id' => $item->id,
                 'size'         => 'large', // ← size مختلفة
                 'quantity'     => 1,
             ]);

        // المفروض يكون عندنا row تاني
        $this->assertEquals(
            2,
            Cart::where('user_id', $user->id)
                ->where('menu_item_id', $item->id)
                ->count()
        );
    }

    /** @test */
    public function test_cannot_add_item_with_unavailable_size(): void
    {
        $user = $this->createCustomer();

        // item عنده small بس
        $item = $this->createMenuItem([
            ['size' => 'small', 'price' => 25.00],
        ]);

        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/cart', [
                             'menu_item_id' => $item->id,
                             'size'         => 'large', // ← مش متاحة
                             'quantity'     => 1,
                         ]);

        $response->assertStatus(422);
    }

    // ==========================================
    // UPDATE CART TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_update_cart_item_quantity(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 1,
        ]);

        $this->actingAs($user, 'api')
             ->patchJson("/api/cart/{$item->id}/medium", ['quantity' => 5])
             ->assertStatus(200);

        $this->assertDatabaseHas('carts', [
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'quantity'     => 5,
        ]);
    }

    /** @test */
    public function test_setting_quantity_to_zero_removes_item(): void
    {
        $user = $this->createCustomer();
        $item = $this->createMenuItem();

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 2,
        ]);

        $response = $this->actingAs($user, 'api')
                         ->patchJson("/api/cart/{$item->id}/medium", ['quantity' => 0]);

        $response->assertStatus(200)
                 ->assertJson(['action' => 'removed']);

        $this->assertDatabaseMissing('carts', [
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
        ]);
    }

    
}
