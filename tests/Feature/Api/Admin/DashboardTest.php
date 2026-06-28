<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Customer;
use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    // ======= Helpers =======
    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createCustomerWithOrder(string $status, float $total): Order
    {
        $user = User::factory()->create();
        Customer::factory()->create(['user_id' => $user->id]);

        return Order::factory()->create([
            'customer_id'  => $user->customer->id,
            'status'       => $status,
            'total_amount' => $total,
        ]);
    }

    private function createMenuItem(): MenuItem
    {
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        return MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);
    }

    // ==========================================
    // DASHBOARD TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_view_dashboard_statistics(): void
    {
        $admin = $this->createAdmin();

        $this->createCustomerWithOrder('pending', 50.00);
        $this->createCustomerWithOrder('delivered', 70.00);
        $this->createCustomerWithOrder('cancelled', 30.00);

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'total_orders',
                         'total_revenue',
                         'orders_by_status',
                         'most_ordered_items',
                     ],
                 ]);
    }

    /** @test */
    public function test_dashboard_total_orders_counts_all_orders(): void
    {
        $admin = $this->createAdmin();

        $this->createCustomerWithOrder('pending', 50.00);
        $this->createCustomerWithOrder('delivered', 70.00);

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/dashboard');

        $response->assertJson(['data' => ['total_orders' => 2]]);
    }

    /** @test */
    public function test_dashboard_total_revenue_excludes_cancelled_orders(): void
    {
        $admin = $this->createAdmin();

        $this->createCustomerWithOrder('delivered', 70.00);
        $this->createCustomerWithOrder('cancelled', 30.00);

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/dashboard');

        $response->assertJson(['data' => ['total_revenue' => 70.00]]);
    }

    /** @test */
    public function test_dashboard_returns_most_ordered_items(): void
    {
        $admin = $this->createAdmin();
        $item  = $this->createMenuItem();
        $order = $this->createCustomerWithOrder('delivered', 100.00);

        OrderDetail::factory()->create([
            'order_id'     => $order->id,
            'menu_item_id' => $item->id,
            'size'         => 'medium',
            'quantity'     => 5,
            'price'        => 20.00,
        ]);

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'most_ordered_items' => [
                             ['menu_item_id' => $item->id, 'name' => $item->name, 'total_sold' => 5],
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function test_non_admin_cannot_access_dashboard(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff, 'api')
             ->getJson('/api/admin/dashboard')
             ->assertStatus(403);
    }
}
