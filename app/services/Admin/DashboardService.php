<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderDetail;
use App\OrderStatus;

class DashboardService
{
    // ======= Statistics =======
    public function getStatistics(): array
    {
        return [
            'total_orders'        => Order::count(),
            'total_revenue'       => (float) Order::where('status', '!=', OrderStatus::CANCELLED->value)->sum('total_amount'),
            'orders_by_status'    => $this->getOrdersByStatus(),
            'most_ordered_items'  => $this->getMostOrderedItems(),
        ];
    }

    private function getOrdersByStatus(): array
    {
        return Order::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getMostOrderedItems(int $limit = 5)
    {
        return OrderDetail::query()
            ->selectRaw('menu_item_id, sum(quantity) as total_quantity')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->with('menuItem')
            ->limit($limit)
            ->get()
            ->map(fn ($detail) => [
                'menu_item_id' => $detail->menu_item_id,
                'name'         => $detail->menuItem?->name,
                'total_sold'   => (int) $detail->total_quantity,
            ]);
    }
}
