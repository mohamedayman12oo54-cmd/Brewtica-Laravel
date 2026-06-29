<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    // ==========================================
    // EXCEPTION HANDLER TESTS
    // ==========================================

    /** @test */
    public function test_returns_json_404_for_nonexistent_route(): void
    {
        $response = $this->getJson('/api/route-that-does-not-exist');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => 'error',
                     'message' => 'This endpoint dose not exist',
                 ]);
    }

    /** @test */
    public function test_returns_json_404_for_missing_model(): void
    {
        $user = User::factory()->create();

        // بنطلب menu item مش موجود
        $this->actingAs($user, 'api')
             ->getJson('/api/menu/items/99999')
             ->assertStatus(404)
             ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function test_returns_json_401_for_unauthenticated_request(): void
    {
        // من غير token
        $this->getJson('/api/profile')
             ->assertStatus(401)
             ->assertJson([
                 'status'  => 'error',
                 'message' => 'You are not authorized.',
             ]);
    }
    /** @test */
    public function test_returns_json_403_for_forbidden_request(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // Customer بيحاول يوصل لـ Admin route
        $this->actingAs($customer, 'api')
             ->getJson('/api/admin/categories')
             ->assertStatus(403)
             ->assertJson([
                 'status'  => 'error',
                 'message' => 'You are not authorized to access this resource.',
             ]);
    }

    /** @test */
    public function test_returns_json_422_for_validation_error(): void
    {
        // Register من غير بيانات
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'errors' => ['f_name', 'email', 'password'],
                 ]);
    }

    /** @test */
    public function test_error_response_never_exposes_stack_trace(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/menu/items/99999');

        // التأكد إن الـ stack trace مش ظاهر
        $response->assertJsonMissing(['file'])
                 ->assertJsonMissing(['line'])
                 ->assertJsonMissing(['trace']);
    }


}
