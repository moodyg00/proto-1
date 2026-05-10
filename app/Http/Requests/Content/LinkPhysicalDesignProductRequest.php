<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class LinkPhysicalDesignProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
