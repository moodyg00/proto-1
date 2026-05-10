<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class PublishBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'published_at' => ['nullable', 'date'],
        ];
    }
}
