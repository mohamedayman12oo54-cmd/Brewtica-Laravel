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
}
