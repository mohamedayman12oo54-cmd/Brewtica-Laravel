<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
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
            'image' => $this->image
                            ? assert('storage/' . $this->image)
                            : null,
            'sub_sub_categories' => SubSubCategoryResource::collection(
                $this->whenLoaded('subSubCategories')
            ),
        ];
    }
}
