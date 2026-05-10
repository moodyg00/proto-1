<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'category'        => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string'],
            'suggested_price' => ['nullable', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
        ];
    }
}
