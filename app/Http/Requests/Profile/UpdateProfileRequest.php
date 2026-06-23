<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'f_name' => ['sometimes', 'string', 'max:50'],
            'l_name' => ['sometimes', 'string', 'max:50'],
            'gender' => ['sometimes', 'in:male,female'],
            'date_of_birth' => ['sometimes', 'date', 'before:today'],
            'street' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100']
        ];
    }
}
