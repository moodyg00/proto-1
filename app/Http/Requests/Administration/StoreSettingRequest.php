<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'module'      => ['required', 'string', 'max:100'],
            'key'         => ['required', 'string', 'max:255'],
            'value'       => ['nullable'],
            'description' => ['nullable', 'string'],
        ];
    }
}
