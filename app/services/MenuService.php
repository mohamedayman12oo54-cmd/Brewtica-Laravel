<?php

namespace App\Services;

use App\Models\Main_Category;
use App\Models\MenuItem;

class MenuService
{
    // ======= All Categories with Hirearchy =======
    public function getAllCategories()
    {
        return Main_Category::with([
            'subCategories.subSubCategories'
        ])->get();
    }

    // ======= All Items With Filtering =======
    public function getItems(array $filters = [])
    {
        $query = MenuItem::with(['sizePrices', 'subSubCategory.subCategory.mainCategory'])
                         ->has('sizePrices');

        // Filter by sub_sub_category
        if(!empty($filters['category_id'])) {
            $query->where('sub_sub_category_id', $filters['category_id']);
        }

        // Search
        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['q']}%")
                  ->orWhere('description', 'like', "%{$filters['q']}%")
                  ->orWhere('ingredients', 'like', "%{$filters['q']}%");
            });
        }

        return $query->get();
    }

    public function getItem(int $id)
    {
        return MenuItem::with([
            'sizePrices', 
            'subSubCategory.subCategory.mainCategory',
        ])
        ->has('sizePrices')
        ->findOrFail($id);
    }
}