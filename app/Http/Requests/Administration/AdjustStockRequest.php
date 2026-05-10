<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'quantity_on_hand'  => ['required', 'numeric', 'min:0'],
            'quantity_reserved' => ['nullable', 'numeric', 'min:0'],
            'reorder_level'     => ['nullable', 'numeric', 'min:0'],
            'location'          => ['nullable', 'string', 'max:255'],
        ];
    }
}
