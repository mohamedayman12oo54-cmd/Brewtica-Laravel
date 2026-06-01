<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory;

    protected $fillable = [
        'sub_sub_category_id',
        'name',
        'description',
        'ingredients',
        'image',
    ];

    // ======= Relationships =======

    public function subSubCategory(): BelongsTo
    {
        return $this->belongsTo(SubSubCategory::class);
    }

    public function sizePrices(): HasMany
    {
        return $this->hasMany(MenuItemSizePrice::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
