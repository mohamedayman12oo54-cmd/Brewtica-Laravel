<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Order;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Support\Facades\DB;

class OrderService
{
    // ======= Create Order from Cart =======
    public function createOrder(User $user): array
    {
        $cartItems = $user->cart()->with(['menuItem.sizePrices'])->get();

        if($cartItems->isEmpty()) {
            return ['success' => false, 'reason' => 'cart_empty'];
        }

        return DB::transaction(function () use ($user, $cartItems) {
            $total = $cartItems->sum(function ($cartItem) {
                $price = $cartItem->menuItem
                                  ->sizePrices
                                  ->firstWhere('size', $cartItem->size)
                                  ?->price ?? 0;
                return $price * $cartItem->quantity;
            });

            $order = Order::create([
                'customer_id'  => $user->customer->id,
                'status'       => 'pending',
                'total_amount' => $total,
            ]);

            foreach ($cartItems as $cartItem) {
                $price = $cartItem->menuItem
                                    ->sizePrices
                                    ->firstWhere('size', $cartItem->size)
                                    ?->price ?? 0;

                $order->orderDetails()->create([
                    'menu_item_id' => $cartItem->menu_item_id,
                    'size'         => $cartItem->size,
                    'quantity'     => $cartItem->quantity,
                    'price'        => $price,
                    // ↑ Snapshot للسعر وقت الشراء
                ]);
            }

            // مسح الـ Cart بعد إنشاء الـ Order
            $user->cart()->delete();

            // بعت Confirmation في الـ Background
            SendOrderConfirmationJob::dispatch($user, $order);

            return ['success' => true, 'order' => $order->load('orderDetails.menuItem')];

        });
    }

    // ======= Get Customer Orders =======
    public function getCustomerOrders(User $user)
    {
        return $user->customer
                    ->orders()
                    ->with(['orderDetails.menuItems'])
                    ->latest()
                    ->get();
    }

    // ======= Get Single Order =======
    public function getOrder(User $user, int $orderId): Order|null
    {
        return $user->customer
                    ->orders()
                    ->with([
                        'orderDetails.menuItem',
                        'payment',
                        'delivery',
                    ])
                    ->find($orderId);
    }

    // ======= Cancel Order =======
    public function cancelOrder(User $user, int $orderId): array
    {
        $order = $user->customer
                      ->orders()
                      ->find($orderId);

        if (!$order) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($order->status !== OrderStatus::PENDING) {
            return ['success' => false, 'reason' => 'cannot_cancel'];
        }

        $order->update(['status' => OrderStatus::CANCELLED]);
        return ['success' => true];
    }
}