<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
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
            'sub_sub_category_id' => ['required', 'integer', 'exists:sub_sub_categories,id'],
            'name'                 => ['required', 'string', 'max:255', 'unique:menu_items,name'],
            'description'          => ['nullable', 'string'],
            'ingredients'          => ['nullable', 'string'],
            'image'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'prices'               => ['required', 'array', 'min:1'],
            'prices.*.size'        => ['required', 'in:small,medium,large', 'distinct'],
            'prices.*.price'       => ['required', 'numeric', 'min:0'],
        ];
    }
}
