<?php

namespace App\Http\Resources\Admin;

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
            'id'                   => $this->id,
            'sub_sub_category_id'  => $this->sub_sub_category_id,
            'name'                 => $this->name,
            'description'          => $this->description,
            'ingredients'          => $this->ingredients,
            'image'                => $this->image ? asset('storage/' . $this->image) : null,
            'category'             => $this->whenLoaded('subSubCategory', function () {
                return [
                    'main'    => $this->subSubCategory->subCategory->mainCategory->name,
                    'sub'     => $this->subSubCategory->subCategory->name,
                    'sub_sub' => $this->subSubCategory->name,
                ];
            }),
            'prices' => $this->whenLoaded('sizePrices', function () {
                return $this->sizePrices->mapWithKeys(fn ($price) => [
                    $price->size->value => $price->price,
                ]);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
