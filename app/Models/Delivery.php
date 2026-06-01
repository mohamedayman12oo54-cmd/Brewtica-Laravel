<?php

namespace App\Models;

use App\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'staff_user_id',
        'address',
        'status',
        'assigned_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at'  => 'datetime',
            'delivered_at' => 'datetime',
            'status' => DeliveryStatus::class,
        ];
    }

    // ======= Relationships =======

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }
}
