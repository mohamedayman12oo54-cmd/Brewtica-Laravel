<?php

namespace Tests\Feature\Api\Profile;

use App\Jobs\SendPasswordChangedNotificationJob;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserPhone;
use Illuminate\Support\Facades\Queue;
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

    /** @test */
    public function test_customer_cannot_update_email_or_role(): void
    {
        $user = $this->createCustomer();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', [
                 'email' => 'hacker@evil.com',
                 'role'  => 'admin',
             ]);


        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'email' => $user->email,
            'role'  => 'customer',
        ]);
    }


    // ==========================================
    // UPDATE PASSWORD TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_change_password_with_correct_current_password(): void
    {
        Queue::fake();

        $user = $this->createCustomer();

        $response = $this->actingAs($user, 'api')
                         ->patchJson('/api/profile/password', [
                             'current_password'      => 'password',
                             'password'              => 'newpassword123',
                             'password_confirmation' => 'newpassword123',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        Queue::assertPushed(SendPasswordChangedNotificationJob::class);
    }

    /** @test */
    public function test_customer_cannot_change_password_with_wrong_current_password(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user, 'api')
                         ->patchJson('/api/profile/password', [
                             'current_password'      => 'wrongpassword',
                             'password'              => 'newpassword123',
                             'password_confirmation' => 'newpassword123',
                         ]);

        $response->assertStatus(422);
    }

    // ==========================================
    // PHONES TESTS
    // ==========================================

    /** @test */
    public function test_customer_can_add_phone(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/profile/phones', [
                             'phone' => '01012345678',
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('user_phones', [
            'user_id' => $user->id,
            'phone'   => '01012345678',
        ]);
    }

    /** @test */
    public function test_first_phone_is_automatically_primary(): void
    {
        $user = $this->createCustomer();

        $this->actingAs($user, 'api')
             ->postJson('/api/profile/phones', ['phone' => '01012345678']);

        $this->assertDatabaseHas('user_phones', [
            'user_id'    => $user->id,
            'phone'      => '01012345678',
            'is_primary' => true,
        ]);
    }

    /** @test */
    public function test_customer_can_set_primary_phone(): void
    {
        $user   = $this->createCustomer();
        $phone1 = UserPhone::factory()->create(['user_id' => $user->id, 'is_primary' => true]);
        $phone2 = UserPhone::factory()->create(['user_id' => $user->id, 'is_primary' => false]);

        $this->actingAs($user, 'api')
             ->patchJson("/api/profile/phones/{$phone2->id}/primary")
             ->assertStatus(200);

        $this->assertDatabaseHas('user_phones', ['id' => $phone2->id, 'is_primary' => true]);
        $this->assertDatabaseHas('user_phones', ['id' => $phone1->id, 'is_primary' => false]);
    }

    /** @test */
    public function test_customer_cannot_delete_only_primary_phone(): void
    {
        $user  = $this->createCustomer();
        $phone = UserPhone::factory()->create([
            'user_id'    => $user->id,
            'is_primary' => true,
        ]);

        $this->actingAs($user, 'api')
             ->deleteJson("/api/profile/phones/{$phone->id}")
             ->assertStatus(422);
    }

    /** @test */
    public function test_customer_cannot_access_another_users_phone(): void
    {
        $user1  = $this->createCustomer();
        $user2  = $this->createCustomer();
        $phone2 = UserPhone::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1, 'api')
             ->deleteJson("/api/profile/phones/{$phone2->id}")
             ->assertStatus(404); 
    }
}
