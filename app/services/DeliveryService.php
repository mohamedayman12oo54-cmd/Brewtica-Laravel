<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\User;

class DeliveryService
{
    // ======= Get My Deliveries =======
    public function getMyDeliveries(User $user)
    {
        return $user->deliveries()
                    ->with(['order.orderDetails.menuItem', 'order.customer.user'])
                    ->latest()
                    ->get();
        // ↑ Scoped: بياخد Deliveries بتاعت الـ Delivery Person ده بس
    }

    // ======= Update Delivery Status =======
    public function updateStatus(User $user, int $deliveryId, string $status): array
    {
        $delivery = $user->deliveries()->find($deliveryId);
        // ↑ Scoped: بنتأكد إن الـ Delivery بتاعت الـ Delivery Person ده

        if (!$delivery) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        $allowed = $this->getAllowedTransitions($delivery->status);

        if (!in_array($status, $allowed)) {
            return ['success' => false, 'reason' => 'invalid_transition'];
        }

        $data = ['status' => $status];

        // لو بقت delivered → نسجل الوقت
        if ($status === 'delivered') {
            $data['delivered_at'] = now();
        }

        $delivery->update($data);
        return ['success' => true, 'delivery' => $delivery->fresh()];
    }

    private function getAllowedTransitions(string $currentStatus): array
    {
        return match($currentStatus) {
            'assigned'   => ['picked_up'],
            'picked_up'  => ['on_the_way'],
            'on_the_way' => ['delivered', 'failed'],
            default      => [],
        };
    }
}