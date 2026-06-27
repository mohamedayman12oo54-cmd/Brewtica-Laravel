<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'total_amount' => $this->total_amount,
            'created_at'   => $this->created_at->format('Y-m-d H:i'),
            'items'        => $this->whenLoaded('orderDetails', function () {
                return $this->orderDetails->map(fn($detail) => [
                    'name'       => $detail->menuItem->name,
                    'size'       => $detail->size,
                    'quantity'   => $detail->quantity,
                    'unit_price' => $detail->price,
                    'subtotal'   => round($detail->price * $detail->quantity, 2),
                ]);
            }),
            'payment'  => $this->whenLoaded('payment', fn() => $this->payment),
            'delivery' => $this->whenLoaded('delivery', fn() => $this->delivery),
        ];
    }
}
