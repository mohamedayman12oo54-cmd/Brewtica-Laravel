<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'size' => ['required', 'in:small,medium,large'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_item_id.required' => 'The menu item is required.',
            'menu_item_id.exists'   => 'The selected menu item is invalid.',
            'size.required'         => 'The size is required.',
            'size.in'               => 'The size must be small, medium, or large.',
            'quantity.min'          => 'The quantity must be at least 1.',
            'quantity.max'          => 'The quantity may not be greater than 99.',
        ];
    }
}
