<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCategory extends Model
{
    /** @use HasFactory<\Database\Factories\SubCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'main_category_id',
        'name',
        'description',
        'image',
    ];

    // ====== Relationships ======

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(Main_Category::class, 'main_category_id');
    }

    public function subSubCategories(): HasMany
    {
        return $this->hasMany(SubSubCategory::class);
    }
}
