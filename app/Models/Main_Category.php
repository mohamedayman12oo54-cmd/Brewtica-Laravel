<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Main_Category extends Model
{
    /** @use HasFactory<\Database\Factories\Main_CategoryFactory> */
    use HasFactory;

    protected $table = 'main_categories';

    protected $fillable = [
        'name',
        'description',
    ];

    // ====== Relationships ======

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'main_category_id');
    }
}
