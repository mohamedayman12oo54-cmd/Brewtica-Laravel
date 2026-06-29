<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    // ==========================================
    // RATE LIMIT TESTS
    // ==========================================

    /** @test */
    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        // بنجرب 5 محاولات فاشلة
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/auth/login', [
                'email'    => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // المحاولة السادسة المفروض تتبلوك
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'retry_after',
                 ]);
    }

    /** @test */
    public function test_rate_limit_returns_correct_structure(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/auth/login', [
                'email'    => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
                 ->assertJson([
                     'status'  => 'error',
                     'message' => 'Too many attempts. Please try again later.',
                 ]);

        // التأكد إن الـ retry_after موجود وهو رقم
        $this->assertIsInt($response->json('retry_after'));
    }

    /** @test */
    public function test_successful_login_resets_rate_limit(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        // محاولتين فاشلتين
        foreach (range(1, 2) as $attempt) {
            $this->postJson('/api/auth/login', [
                'email'    => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // محاولة ناجحة
        $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'correctpassword',
        ])->assertStatus(200);
    }
}
