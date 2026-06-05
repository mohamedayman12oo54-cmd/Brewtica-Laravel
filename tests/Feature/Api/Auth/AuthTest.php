<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\UserGender;
use App\UserRole;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // ==========================================
    // REGISTER TESTS
    // ========================================== 

    /** @test */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'f_name' => 'Mohamed',
            'l_name' => 'Ayman',
            'email' => 'mohamedayman@gmail.com',
            'password' => 'mohamed123',
            'password_confirmation' => 'mohamed123',
            'gender' => UserGender::MALE,
            'date_of_birth' => '2005-01-29',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'status',
                    'message',
                    'user' => ['id', 'f_name', 'l_name', 'email', 'role'],
                    'token',
                    'token_type',
                    'expires_in',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'mohamedayman@gmail.com',
            'role' => UserRole::CUSTOMER,
        ]);

        $this->assertDatabaseHas('customers', [
            'user_id' => $response->json('user.id'),
        ]);

        $response->assertJsonMissing(['password']);
    }

    /** @test */
    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'mohamedayman@gmail.com']);

        $response = $this->postJson('/api/auth/register', [
            'f_name' => 'Mohamed',
            'l_name' => 'Ayman',
            'email' => 'mohamedayman@gmail.com',
            'password' => 'mohamed123',
            'password_confirmation' => 'mohamed123',
            'gender' => UserGender::MALE,
            'date_of_birth' => '2005-01-29',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

    }

    /** @test */
    public function test_user_cannot_register_with_weak_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'f_name' => 'Mohamed',
            'l_name' => 'Ayman',
            'email' => 'mohamedayman@gmail.com',
            'password' => '123',
            'password_confirmation' => '123',
            'gender' => UserGender::MALE,
            'date_of_birth' => '2005-01-29',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function test_user_cannot_register_with_mismatch_passwords(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'f_name' => 'Mohamed',
            'l_name' => 'Ayman',
            'email' => 'mohamedayman@gmail.com',
            'password' => 'mohamed123',
            'password_confirmation' => 'mohamed0123',
            'gender' => UserGender::MALE,
            'date_of_birth' => '2005-01-29',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    // ==========================================
    // LOGIN TESTS
    // ==========================================

    /** @test */
    public function test_user_can_login_with_valid_cardentials(): void
    {
        $user = User::factory()->create([
            'email' => 'mohamedayman@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'mohamedayman@gmail.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'status',
                    'token',
                    'token_type',
                    'expires_in',
                 ]);
    }

    /** @test */
    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'mohamedayman@gmail.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'mohamedayman@gmail.com',
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_user_cannot_login_with_nonexisting_email(): void
    {
        $response = $this->postJson('api/auth/login', [
            'email' => 'nonexistingEmail@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    // ==========================================
    // ME TESTS
    // ==========================================

    /** @test */
    public function test_authenticated_user_can_get_their_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/auth/me');

        $response->assertStatus(200)
                 ->assertJson([
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'f_name' => $user->f_name,
                        'l_name' => $user->l_name,
                    ],
                 ]);

    }

    /** @test */
    public function test_unauthenticated_user_cannot_get_their_data(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    // ==========================================
    // LOGOUT TESTS
    // ==========================================

    /** @test */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }
    

}
