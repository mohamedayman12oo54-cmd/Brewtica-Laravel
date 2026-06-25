<?php

namespace App\Http\Resources\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
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
            'menu_item' => [
                'id' => $this->menuItem->id,
                'name' => $this->menuItem->name,
                'description' => $this->menuItem->description,
                'image' => $this->menuItem->image
                                ? asset('storage/' . $this->menuItem->image)
                                : null,
                'prices' => $this->menuItem->sizePrices
                                 ->pluck('size', 'price'),
            ],
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
