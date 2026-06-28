<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sub_sub_category_id' => ['sometimes', 'integer', 'exists:sub_sub_categories,id'],
            'name'                 => ['sometimes', 'string', 'max:255', Rule::unique('menu_items', 'name')->ignore($this->route('id'))],
            'description'          => ['sometimes', 'nullable', 'string'],
            'ingredients'          => ['sometimes', 'nullable', 'string'],
            'image'                => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'prices'               => ['sometimes', 'array', 'min:1'],
            'prices.*.size'        => ['required_with:prices', 'in:small,medium,large', 'distinct'],
            'prices.*.price'       => ['required_with:prices', 'numeric', 'min:0'],
        ];
    }
}
