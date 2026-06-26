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
}
