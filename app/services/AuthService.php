<?php

namespace App\services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    // ======= Register =======
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'f_name'        => $data['f_name'],
                'l_name'        => $data['l_name'],
                'email'         => $data['email'],
                'password'      => $data['password'],
                'gender'        => $data['gender'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'role'          => 'customer', // ← default role دايماً
            ]);

            Customer::create([
                'user_id'        => $user->id,
                'loyalty_points' => 0,
            ]);

            $token = auth('api')->login($user);

            return $this->buildTokenResponse($user, $token);
        });
    }

    // ======= Login =======
    public function login(array $credentials): array|false
    {
        $token = auth('api')->attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ]);

        if (!$token) {
            return false;
        }

        return $this->buildTokenResponse(auth('api')->user(), $token);
    }

    // ======= Refresh =======
    public function refresh(): array
    {
        $token = auth('api')->refresh();

        return $this->buildTokenResponse(auth('api')->user(), $token);
    }

    // ======= Helper =======
    private function buildTokenResponse(User $user, string $token): array
    {
        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}