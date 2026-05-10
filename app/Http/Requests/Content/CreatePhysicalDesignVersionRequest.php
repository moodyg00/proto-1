<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class CreatePhysicalDesignVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version_number' => ['nullable', 'string', 'max:20'],
            'files' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,approved,archived'],
        ];
    }
}
