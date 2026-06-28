<?php

namespace App\Http\Resources\Admin;

use App\Models\Main_Category;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return match (true) {
            $this->resource instanceof Main_Category   => $this->mainCategory(),
            $this->resource instanceof SubCategory      => $this->subCategory(),
            $this->resource instanceof SubSubCategory   => $this->subSubCategory(),
        };
    }

    private function mainCategory(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'sub_categories' => CategoryResource::collection($this->whenLoaded('subCategories')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }

    private function subCategory(): array
    {
        return [
            'id'                 => $this->id,
            'main_category_id'   => $this->main_category_id,
            'name'               => $this->name,
            'description'        => $this->description,
            'image'              => $this->image ? asset('storage/' . $this->image) : null,
            'sub_sub_categories' => CategoryResource::collection($this->whenLoaded('subSubCategories')),
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }

    private function subSubCategory(): array
    {
        return [
            'id'               => $this->id,
            'sub_category_id'  => $this->sub_category_id,
            'name'             => $this->name,
            'description'      => $this->description,
            'image'            => $this->image ? asset('storage/' . $this->image) : null,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
