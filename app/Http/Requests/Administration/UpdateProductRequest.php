<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'category'         => ['required', 'string', 'max:100'],
            'sku'              => ['nullable', 'string', 'max:100'],
            'description'      => ['nullable', 'string'],
            'unit_price'       => ['nullable', 'numeric', 'min:0'],
            'is_for_sale'      => ['boolean'],
            'is_internal_use'  => ['boolean'],
            'inventory.quantity_on_hand' => ['nullable', 'numeric', 'min:0'],
            'inventory.reorder_level'    => ['nullable', 'numeric', 'min:0'],
            'inventory.location'         => ['nullable', 'string', 'max:255'],
        ];
    }
}
