<?php

namespace App\Services\Admin;

use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    // ======= Main Categories =======
    public function getMainCategories()
    {
        return Main_Category::with('subCategories.subSubCategories')->get();
    }

    public function createMainCategory(array $data): Main_Category
    {
        return Main_Category::create($data);
    }

    public function updateMainCategory(int $id, array $data): array
    {
        $category = Main_Category::find($id);

        if (!$category) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        $category->update($data);
        return ['success' => true, 'category' => $category];
    }

    public function deleteMainCategory(int $id): array
    {
        $category = Main_Category::find($id);

        if (!$category) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        $hasItems = MenuItem::whereHas('subSubCategory.subCategory', function ($q) use ($category) {
            $q->where('main_category_id', $category->id);
        })->exists();

        if ($hasItems) {
            return ['success' => false, 'reason' => 'has_items'];
        }

        $category->delete();
        return ['success' => true];
    }

    // ======= Sub Categories =======
    public function getSubCategories(array $filters = [])
    {
        $query = SubCategory::with('subSubCategories');

        if (!empty($filters['main_category_id'])) {
            $query->where('main_category_id', $filters['main_category_id']);
        }

        return $query->get();
    }

    public function createSubCategory(array $data, ?UploadedFile $image = null): SubCategory
    {
        if ($image) {
            $data['image'] = $this->storeImage($image, 'sub-categories');
        }

        return SubCategory::create($data);
    }

    public function updateSubCategory(int $id, array $data, ?UploadedFile $image = null): array
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($image) {
            $this->deleteImage($subCategory->image);
            $data['image'] = $this->storeImage($image, 'sub-categories');
        }

        $subCategory->update($data);
        return ['success' => true, 'category' => $subCategory];
    }

    public function deleteSubCategory(int $id): array
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        $hasItems = MenuItem::whereHas('subSubCategory', function ($q) use ($subCategory) {
            $q->where('sub_category_id', $subCategory->id);
        })->exists();

        if ($hasItems) {
            return ['success' => false, 'reason' => 'has_items'];
        }

        $this->deleteImage($subCategory->image);
        $subCategory->delete();
        return ['success' => true];
    }

    // ======= Sub-Sub Categories =======
    public function getSubSubCategories(array $filters = [])
    {
        $query = SubSubCategory::query();

        if (!empty($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        return $query->get();
    }

    public function createSubSubCategory(array $data, ?UploadedFile $image = null): SubSubCategory
    {
        if ($image) {
            $data['image'] = $this->storeImage($image, 'sub-sub-categories');
        }

        return SubSubCategory::create($data);
    }

    public function updateSubSubCategory(int $id, array $data, ?UploadedFile $image = null): array
    {
        $subSubCategory = SubSubCategory::find($id);

        if (!$subSubCategory) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($image) {
            $this->deleteImage($subSubCategory->image);
            $data['image'] = $this->storeImage($image, 'sub-sub-categories');
        }

        $subSubCategory->update($data);
        return ['success' => true, 'category' => $subSubCategory];
    }

    public function deleteSubSubCategory(int $id): array
    {
        $subSubCategory = SubSubCategory::find($id);

        if (!$subSubCategory) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($subSubCategory->menuItems()->exists()) {
            return ['success' => false, 'reason' => 'has_items'];
        }

        $this->deleteImage($subSubCategory->image);
        $subSubCategory->delete();
        return ['success' => true];
    }

    // ======= Helpers =======
    private function storeImage(UploadedFile $image, string $folder): string
    {
        return $image->store($folder, 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
