<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\StaffDetail;
use App\Models\User;
use Tests\TestCase;

class StaffTest extends TestCase
{
    // ======= Helpers =======
    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function staffPayload(array $overrides = []): array
    {
        return array_merge([
            'f_name'     => 'John',
            'l_name'     => 'Doe',
            'email'      => 'john.doe@example.com',
            'password'   => 'password123',
            'role'       => 'staff',
            'job_title'  => 'Barista',
            'salary'     => 4000,
            'hire_date'  => '2026-01-01',
            'shift'      => 'morning',
            'department' => 'Operations',
        ], $overrides);
    }

    // ==========================================
    // LIST TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_view_all_users(): void
    {
        $admin = $this->createAdmin();
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/staff');

        $response->assertStatus(200)
                 ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = $this->createAdmin();
        User::factory()->staff()->create();
        User::factory()->create();

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/staff?role=staff');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    // ==========================================
    // CREATE TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_create_staff_user(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'api')
                         ->postJson('/api/admin/staff', $this->staffPayload());

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com', 'role' => 'staff']);
        $this->assertDatabaseHas('staff_details', ['job_title' => 'Barista']);
    }

    /** @test */
    public function test_creating_staff_requires_unique_email(): void
    {
        $admin = $this->createAdmin();
        User::factory()->create(['email' => 'john.doe@example.com']);

        $this->actingAs($admin, 'api')
             ->postJson('/api/admin/staff', $this->staffPayload())
             ->assertStatus(422);
    }

    /** @test */
    public function test_creating_staff_rejects_customer_role(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'api')
             ->postJson('/api/admin/staff', $this->staffPayload(['role' => 'customer']))
             ->assertStatus(422);
    }

    // ==========================================
    // UPDATE TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_update_user_profile(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->staff()->create();
        StaffDetail::factory()->create(['user_id' => $staff->id]);

        $this->actingAs($admin, 'api')
             ->patchJson("/api/admin/staff/{$staff->id}", ['f_name' => 'Updated'])
             ->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'f_name' => 'Updated']);
    }

    /** @test */
    public function test_admin_can_change_user_role(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->staff()->create();
        StaffDetail::factory()->create(['user_id' => $staff->id]);

        $this->actingAs($admin, 'api')
             ->patchJson("/api/admin/staff/{$staff->id}", ['role' => 'delivery'])
             ->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'role' => 'delivery']);
    }

    // ==========================================
    // DELETE TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_delete_user_without_orders(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->staff()->create();
        StaffDetail::factory()->create(['user_id' => $staff->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/staff/{$staff->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/staff/{$admin->id}")
             ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_customer_with_orders(): void
    {
        $admin    = $this->createAdmin();
        $customer = User::factory()->create();
        Customer::factory()->create(['user_id' => $customer->id]);
        Order::factory()->create(['customer_id' => $customer->customer->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/staff/{$customer->id}")
             ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_delivery_staff_with_assigned_deliveries(): void
    {
        $admin    = $this->createAdmin();
        $delivery = User::factory()->delivery()->create();
        $customer = User::factory()->create();
        Customer::factory()->create(['user_id' => $customer->id]);
        $order = Order::factory()->create(['customer_id' => $customer->customer->id]);

        Delivery::create([
            'order_id'      => $order->id,
            'staff_user_id' => $delivery->id,
            'address'       => '123 Main St',
        ]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/staff/{$delivery->id}")
             ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $delivery->id]);
    }

    /** @test */
    public function test_non_admin_cannot_access_staff_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff, 'api')
             ->getJson('/api/admin/staff')
             ->assertStatus(403);
    }
}
