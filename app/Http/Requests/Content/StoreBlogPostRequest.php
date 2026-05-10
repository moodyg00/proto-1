<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'featured_image_url' => ['nullable', 'string'],
            'author_id' => ['nullable', 'uuid', 'exists:users,id'],
            'category_id' => ['nullable', 'uuid', 'exists:blog_categories,id'],
            'category' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'array'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
