<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubSubCategory extends Model
{
    /** @use HasFactory<\Database\Factories\SubSubCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'sub_category_id',
        'name',
        'description',
    ];

    // ====== Relationships ======

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
