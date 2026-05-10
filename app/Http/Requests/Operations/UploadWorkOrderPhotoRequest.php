<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UploadWorkOrderPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo_url' => ['required', 'url'],
            'description' => ['nullable', 'string'],
        ];
    }
}
