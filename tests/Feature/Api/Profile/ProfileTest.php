<?php

namespace Tests\Feature\Api\Profile;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    private function createCustomer(): User
    {
        $user = User::factory()->create();
        Customer::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    // ==========================================
    // SHOW PROFILE TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_view_their_profile(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/profile');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'id', 'f_name', 'l_name', 'email',
                         'gender', 'role', 'loyalty_points',
                         'phones',
                     ]
                 ]);
    }

    /** @test */
    public function test_guest_cannot_view_profile(): void
    {
        $this->getJson('/api/profile')
             ->assertStatus(401);
    }

    // ==========================================
    // UPDATE PROFILE TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_update_their_profile(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user, 'api')
                         ->patchJson('/api/profile', [
                             'f_name' => 'NewName',
                             'city'   => 'Alexandria',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'f_name' => 'NewName',
        ]);

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'city'    => 'Alexandria',
        ]);
    }
}
