<?php

namespace Tests\Feature\Api;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\MenuItemSizePrice;
use App\Models\Order;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderTest extends TestCase
{
    // ======= Helpers =======
    private function createCustomer(): User
    {
        $user = User::factory()->create();
        Customer::factory()->create(['user_id' => $user->id]);
        return $user;
    }

    private function createStaff(): User
    {
        return User::factory()->staff()->create();
    }

    private function addItemToCart(User $user, float $price = 35.00): void
    {
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        $item   = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        MenuItemSizePrice::factory()->create([
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'price'        => $price,
        ]);

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 2,
        ]);
    }

    // ==========================================
    // CREATE ORDER TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_create_order_from_cart(): void
    {
        Queue::fake();

        $user = $this->createCustomer();
        $this->addItemToCart($user, 35.00); // 35 × 2 = 70

        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/orders');

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'data' => ['id', 'status', 'total_amount', 'items'],
                 ]);

        // Order اتعملت في الـ DB
        $this->assertDatabaseHas('orders', [
            'customer_id'  => $user->customer->id,
            'status'       => 'pending',
            'total_amount' => 70.00,
        ]);

        // Cart اتمسحت بعد الـ Order
        $this->assertEquals(0, Cart::where('user_id', $user->id)->count());

        // Confirmation Job اتبعت
        Queue::assertPushed(SendOrderConfirmationJob::class);
    }

    /** @test */
    public function test_customer_cannot_create_order_with_empty_cart(): void
    {
        $user = $this->createCustomer();
        // Cart فارغة

        $this->actingAs($user, 'api')
             ->postJson('/api/orders')
             ->assertStatus(422)
             ->assertJson(['message' => 'Empty cart']);
    }

    /** @test */
    public function test_order_saves_price_snapshot_not_current_price(): void
    {
        Queue::fake();

        $user   = $this->createCustomer();
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        $item   = MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $sizePrice = MenuItemSizePrice::factory()->create([
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'price'        => 35.00,
        ]);

        Cart::create([
            'user_id'      => $user->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 1,
        ]);

        // عمل Order بسعر 35
        $this->actingAs($user, 'api')->postJson('/api/orders');

        // السعر اتغير بعدين
        $sizePrice->update(['price' => 99.00]);

        // Order Detail لازم يفضل بالسعر القديم
        $this->assertDatabaseHas('order_details', [
            'menu_item_id' => $item->id,
            'price'        => 35.00, // ← مش 99
        ]);
    }

    // ==========================================
    // GET ORDERS TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_view_their_orders(): void
    {
        $user = $this->createCustomer();

        Order::factory()->create(['customer_id' => $user->customer->id]);

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/orders');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => ['id', 'status', 'total_amount', 'created_at']
                     ]
                 ]);
    }

    /** @test */
    public function test_customer_sees_only_their_own_orders(): void
    {
        $user1 = $this->createCustomer();
        $user2 = $this->createCustomer();

        Order::factory()->create(['customer_id' => $user2->customer->id]);

        $response = $this->actingAs($user1, 'api')
                         ->getJson('/api/orders');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    // ==========================================
    // CANCEL ORDER TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_cancel_pending_order(): void
    {
        $user  = $this->createCustomer();
        $order = Order::factory()->create([
            'customer_id' => $user->customer->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($user, 'api')
             ->deleteJson("/api/orders/{$order->id}")
             ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function test_customer_cannot_cancel_preparing_order(): void
    {
        $user  = $this->createCustomer();
        $order = Order::factory()->create([
            'customer_id' => $user->customer->id,
            'status'      => 'preparing', // ← مش pending
        ]);

        $this->actingAs($user, 'api')
             ->deleteJson("/api/orders/{$order->id}")
             ->assertStatus(422);
    }

    /** @test */
    public function test_customer_cannot_cancel_another_users_order(): void
    {
        $user1 = $this->createCustomer();
        $user2 = $this->createCustomer();

        $order = Order::factory()->create([
            'customer_id' => $user2->customer->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($user1, 'api')
             ->deleteJson("/api/orders/{$order->id}")
             ->assertStatus(404); // ← مش شايف الـ Order دي أصلاً
    }

    // ==========================================
    // STAFF TESTS
    // ==========================================

    /** @test */
    public function test_staff_can_view_all_orders(): void
    {
        $staff    = $this->createStaff();
        $customer = $this->createCustomer();

        Order::factory()->count(3)->create(['customer_id' => $customer->customer->id]);

        $this->actingAs($staff, 'api')
             ->getJson('/api/staff/orders')
             ->assertStatus(200);
    }

    /** @test */
    public function test_staff_can_update_order_status(): void
    {
        $staff    = $this->createStaff();
        $customer = $this->createCustomer();

        $order = Order::factory()->create([
            'customer_id' => $customer->customer->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($staff, 'api')
             ->patchJson("/api/staff/orders/{$order->id}/status", [
                 'status' => 'preparing',
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'preparing',
        ]);
    }

    /** @test */
    public function test_staff_cannot_make_invalid_status_transition(): void
    {
        $staff    = $this->createStaff();
        $customer = $this->createCustomer();

        $order = Order::factory()->create([
            'customer_id' => $customer->customer->id,
            'status'      => 'pending',
        ]);

        // مش ممكن يروح من pending لـ delivered مباشرة
        $this->actingAs($staff, 'api')
             ->patchJson("/api/staff/orders/{$order->id}/status", [
                 'status' => 'delivered',
             ])
             ->assertStatus(422);
    }

    /** @test */
    public function test_customer_cannot_access_staff_routes(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'api')
             ->getJson('/api/staff/orders')
             ->assertStatus(403);
    }
}
