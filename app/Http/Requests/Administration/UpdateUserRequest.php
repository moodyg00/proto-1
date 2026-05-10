<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        return [
            'full_name'   => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'username'    => ['nullable', 'string', 'max:100', "unique:users,username,{$userId}"],
            'user_type'   => ['required', 'in:human,ai_agent,automation'],
            'role'        => ['required', 'in:user,admin,moderator'],
            'ai_model'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'password'    => ['nullable', 'string', 'min:8'],
            'is_active'   => ['boolean'],
        ];
    }
}
