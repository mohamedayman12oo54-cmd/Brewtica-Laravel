<?php

namespace App\Services;

use App\Models\Order;
use App\OrderStatus;

class StaffOrderService
{
    // ======= Get All Orders =======
    public function getAllOrders(array $filters = [])
    {
        $query = Order::with([
            'orderDetails.menuItem',
            'customer.user',
        ])->latest();

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    // ======= Update Order Status =======
    public function updateStatus(int $orderId, string $status): array
    {
        $order = Order::find($orderId);

        if (!$order) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        // التأكد من الـ Status Transition المنطقي
        $allowed = $this->getAllowedTransitions($order->status);

        if (!in_array($status, $allowed)) {
            return ['success' => false, 'reason' => 'invalid_transition'];
        }

        $order->update(['status' => $status]);
        return ['success' => true, 'order' => $order->fresh()];
    }

    // ======= Status Transition Logic =======
    private function getAllowedTransitions(OrderStatus|string $currentStatus): array
    {
        $currentStatus = $currentStatus instanceof OrderStatus ? $currentStatus->value : $currentStatus;

        return match($currentStatus) {
            'pending'          => ['preparing', 'cancelled'],
            'preparing'        => ['ready'],
            'ready'            => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
            default            => [],
        };
        // ↑ مينفعش ترجع للـ status اللي قبله
    }
}