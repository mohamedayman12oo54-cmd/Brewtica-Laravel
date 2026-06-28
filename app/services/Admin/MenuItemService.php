<?php

namespace App\Services\Admin;

use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuItemService
{
    // ======= Get All Items =======
    public function getItems(array $filters = [])
    {
        $query = MenuItem::with(['sizePrices', 'subSubCategory.subCategory.mainCategory']);

        if (!empty($filters['sub_sub_category_id'])) {
            $query->where('sub_sub_category_id', $filters['sub_sub_category_id']);
        }

        return $query->latest()->get();
    }

    // ======= Create Item =======
    public function createItem(array $data, ?UploadedFile $image = null): MenuItem
    {
        return DB::transaction(function () use ($data, $image) {
            if ($image) {
                $data['image'] = $this->storeImage($image);
            }

            $prices = $data['prices'];
            unset($data['prices']);

            $item = MenuItem::create($data);

            foreach ($prices as $price) {
                $item->sizePrices()->create([
                    'size'  => $price['size'],
                    'price' => $price['price'],
                ]);
            }

            return $item->load(['sizePrices', 'subSubCategory.subCategory.mainCategory']);
        });
    }

    // ======= Update Item =======
    public function updateItem(int $id, array $data, ?UploadedFile $image = null): array
    {
        $item = MenuItem::find($id);

        if (!$item) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        DB::transaction(function () use ($item, $data, $image) {
            if ($image) {
                $this->deleteImage($item->image);
                $data['image'] = $this->storeImage($image);
            }

            $prices = $data['prices'] ?? null;
            unset($data['prices']);

            $item->update($data);

            if ($prices !== null) {
                foreach ($prices as $price) {
                    $item->sizePrices()->updateOrCreate(
                        ['size' => $price['size']],
                        ['price' => $price['price']]
                    );
                }
            }
        });

        return ['success' => true, 'item' => $item->load(['sizePrices', 'subSubCategory.subCategory.mainCategory'])];
    }

    // ======= Delete Item =======
    public function deleteItem(int $id): array
    {
        $item = MenuItem::find($id);

        if (!$item) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        if ($item->orderDetails()->exists()) {
            return ['success' => false, 'reason' => 'has_orders'];
        }

        $this->deleteImage($item->image);
        $item->delete();
        return ['success' => true];
    }

    // ======= Helpers =======
    private function storeImage(UploadedFile $image): string
    {
        return $image->store('menu-items', 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
