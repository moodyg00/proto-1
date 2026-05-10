<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name'   => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'username'    => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'user_type'   => ['required', 'in:human,ai_agent,automation'],
            'role'        => ['required', 'in:user,admin,moderator'],
            'ai_model'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'password'    => ['required_if:user_type,human', 'nullable', 'string', 'min:8'],
            'is_active'   => ['boolean'],
        ];
    }
}
