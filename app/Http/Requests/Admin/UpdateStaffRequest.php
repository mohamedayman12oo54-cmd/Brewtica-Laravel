<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateStaffRequest extends FormRequest
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
            'f_name'        => ['sometimes', 'string', 'max:50'],
            'l_name'        => ['sometimes', 'string', 'max:50'],
            'email'         => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($this->route('id'))],
            'password'      => ['sometimes', 'string', Password::min(8)],
            'role'          => ['sometimes', 'in:admin,staff,delivery'],
            'gender'        => ['sometimes', 'in:male,female'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'job_title'     => ['sometimes', 'string', 'max:255'],
            'salary'        => ['sometimes', 'numeric', 'min:0'],
            'hire_date'     => ['sometimes', 'date'],
            'shift'         => ['sometimes', 'in:morning,evening,night'],
            'department'    => ['sometimes', 'string', 'max:255'],
        ];
    }
}
