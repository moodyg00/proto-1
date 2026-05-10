<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class StorePhysicalDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'design_type' => ['required', 'in:t_shirt,business_card,flyer,sticker,door_hanger,other'],
            'description' => ['nullable', 'string'],
            'files' => ['nullable', 'array'],
            'dimensions' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:draft,approved,archived'],
        ];
    }
}
