<?php

namespace App\Models;

use App\OrderSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemSizePrice extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemSizePriceFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'menu_item_id',
        'size',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'size' => OrderSize::class,
        ];
    }

    // ======= Relationships =======

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
