<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_slug' => ['required', 'string', 'max:180'],
            'page_title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'sections' => ['required', 'array'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
