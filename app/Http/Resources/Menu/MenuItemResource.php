<?php

namespace App\Http\Resources\Menu;

use App\OrderSize;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
                            ? assert('storage/' . $this->image)
                            : null,
            'category'    => $this->whenLoaded('subSubCategory', function () {
                return $this->subSubCategory->name;
            }),
            'prices' => $this->whenLoaded('sizePrices', function() {
                return $this->sizePrices->mapWithKeys(function ($item) {
                    return [$item->size->value => $item->price];
                })->toArray();
            }),
        ];
    }
}
