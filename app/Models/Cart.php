<?php

namespace App\Models;

use App\OrderSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory;

    protected $primaryKey = ['user_id', 'menu_item_id', 'size']; // ← composite PK
    public $incrementing = false; // ← مش auto increment
    public $timestamps = false;   // ← عندنا added_at بدل timestamps

    protected $fillable = [
        'user_id',
        'menu_item_id',
        'size',
        'quantity',
        'added_at',
    ];

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
            'size' => OrderSize::class,
        ];
    }

    // ======= Relationships =======

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
