<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
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
            'f_name'        => ['required', 'string', 'max:50'],
            'l_name'        => ['required', 'string', 'max:50'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'string', Password::min(8)],
            'role'          => ['required', 'in:admin,staff,delivery'],
            'gender'        => ['sometimes', 'in:male,female'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'job_title'     => ['required', 'string', 'max:255'],
            'salary'        => ['required', 'numeric', 'min:0'],
            'hire_date'     => ['required', 'date'],
            'shift'         => ['required', 'in:morning,evening,night'],
            'department'    => ['required', 'string', 'max:255'],
        ];
    }
}
