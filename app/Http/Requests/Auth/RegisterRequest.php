<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'f_name' => ['required', 'string', 'max:50'],
            'l_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'f_name.required' => 'The first name required',
            'l_name.required' => 'The last name required',
            'email.required' => 'The email required',
            'email.unique' => 'The email is already used',
            'password.required' => 'The password required',
            'password.min' => 'The password must be at least 8 charcters',
            'password.confirmed' => 'The password dose not match',
        ];
    }
}
