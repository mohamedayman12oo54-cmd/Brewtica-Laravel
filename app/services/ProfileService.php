<?php

namespace App\services;

use App\Jobs\SendPasswordChangedNotificationJob;
use App\Models\User;
use App\Models\UserPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    // ======= Get Profile =======
    public function getProfile(User $user): User
    {
        return $user->load([
            'phones',
            'customer',
        ]);
    }

    // ======= Update Profile =======
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function() use($user, $data) {
            // User's data
            $user->update(array_filter([
                'f_name' => $data['f_name'] ?? null,
                'l_name' => $data['l_name'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null
            ]));

            // Customer's data
            if($user->customer){
                $user->customer->update(array_filter([
                    'street' => $data['street'] ?? null,
                    'city' => $data['city'] ?? null,
                ]));
            }

            return $user->fresh(['phones', 'customer']);
        });
    }

    // ======= Update Password =======
    public function updatePassword(User $user, array $data): bool
    {
        if(!Hash::check($data['current_password'], $user->password)){
            return false;
        };

        $user->update(['password' => $data['password']]);

        // Send Notification in background
        SendPasswordChangedNotificationJob::dispatch($user);

        return true;
    }

    // ======= Store Phone =======
    public function storePhone(User $user, string $phone): UserPhone
    {
        $isPrimary = $user->phones()->count() === 0;

        return $user->phones()->create([
            'phone' => $phone,
            'is_primary' => $isPrimary,
        ]);
    }

    // ======= Set Primary Phone =======
    public function setPrimaryPhone(User $user, int $phoneId): bool
    {
        $phone = $user->phones()->find($phoneId);

        if(!$phone){
            return false;
        }

        DB::transaction(function() use ($user, $phone){
            $user->phones()->update(['is_primary' => false]);

            $phone->update(['is_primary' => true]);
        });

        return true;
    }

    // ======= Delete Phone =======
    public function deletePhone(User $user, int $phoneId): bool|string
    {
        $phone = $user->phones()->find($phoneId);

        if (!$phone) {
            return 'not_found';
        }

        if ($phone->is_primary && $user->phones()->count() === 1) {
            return 'cannot_delete_only_primary';
        }

        $phone->delete();
        return true;
    }
}