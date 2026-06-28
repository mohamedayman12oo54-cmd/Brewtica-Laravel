<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubCategoryRequest extends FormRequest
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
            'main_category_id' => ['sometimes', 'integer', 'exists:main_categories,id'],
            'name'              => ['sometimes', 'string', 'max:255', Rule::unique('sub_categories', 'name')->ignore($this->route('id'))],
            'description'       => ['sometimes', 'nullable', 'string'],
            'image'             => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
