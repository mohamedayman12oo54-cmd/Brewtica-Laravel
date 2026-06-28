<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class StaffService
{
    private const USER_FIELDS = ['f_name', 'l_name', 'email', 'password', 'role', 'gender', 'date_of_birth'];
    private const STAFF_DETAIL_FIELDS = ['job_title', 'salary', 'hire_date', 'shift', 'department'];

    // ======= Get All Users =======
    public function getAllUsers(array $filters = [])
    {
        $query = User::with(['staffDetail', 'customer']);

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->latest()->get();
    }

    // ======= Create Staff User =======
    public function createStaff(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create(array_intersect_key($data, array_flip(self::USER_FIELDS)));

            $user->staffDetail()->create(array_intersect_key($data, array_flip(self::STAFF_DETAIL_FIELDS)));

            return $user->load('staffDetail');
        });
    }

    // ======= Update User =======
    public function updateUser(int $id, array $data): array
    {
        $user = User::find($id);

        if (!$user) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        DB::transaction(function () use ($user, $data) {
            $userData = array_intersect_key($data, array_flip(self::USER_FIELDS));
            if (!empty($userData)) {
                $user->update($userData);
            }

            $staffData = array_intersect_key($data, array_flip(self::STAFF_DETAIL_FIELDS));
            if (!empty($staffData)) {
                $user->staffDetail()->updateOrCreate([], $staffData);
            }
        });

        return ['success' => true, 'user' => $user->load('staffDetail')];
    }

    // ======= Delete User =======
    public function deleteUser(int $authUserId, int $id): array
    {
        if ($authUserId === $id) {
            return ['success' => false, 'reason' => 'self_delete'];
        }

        $user = User::find($id);

        if (!$user) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($user->customer && $user->customer->orders()->exists()) {
            return ['success' => false, 'reason' => 'has_orders'];
        }

        if ($user->deliveries()->exists()) {
            return ['success' => false, 'reason' => 'has_deliveries'];
        }

        $user->delete();
        return ['success' => true];
    }
}
