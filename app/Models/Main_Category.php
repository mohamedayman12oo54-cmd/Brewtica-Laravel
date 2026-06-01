<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Main_Category extends Model
{
    /** @use HasFactory<\Database\Factories\MainCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // ====== Relationships ======

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }
}
