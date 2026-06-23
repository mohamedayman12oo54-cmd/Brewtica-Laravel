<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'image' => $this->image
                            ? asset('storage/' . $this->image)
                            : null,
            'category'    => $this->whenLoaded('subSubCategory', function () {
                $subSub = $this->subSubCategory;
                $sub = $subSub->subCategory;
                $main = $sub->mainCategory;

                return [
                    'main' => $main->name,
                    'sub' => $sub->name,
                    'sub_sub' => $subSub->name,
                ];
            }),

            'prices' => $this->whenLoaded('sizePrices', function() {
                return $this->sizePrices->mapWithKeys(function ($item) {
                    $key = is_object($item->size) && isset($item->size->value) ? $item->size->value : (string) $item->size;
                    return [$key => $item->price];
                });
            }),
        ];
    }
}
