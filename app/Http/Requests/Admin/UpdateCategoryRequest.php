<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
            'name'        => ['sometimes', 'string', 'max:255', Rule::unique('main_categories', 'name')->ignore($this->route('id'))],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
