<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['nullable', 'uuid', 'exists:contacts,id'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'organization_id' => ['nullable', 'uuid', 'exists:organizations,id'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'subtotal' => ['required', 'numeric', 'gte:0'],
            'tax_amount' => ['nullable', 'numeric', 'gte:0'],
            'total_amount' => ['required', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
            'line_items' => ['nullable', 'array'],
            'line_items.*.description' => ['nullable', 'string'],
            'line_items.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'line_items.*.unit_price' => ['nullable', 'numeric', 'gte:0'],
            'line_items.*.total' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}
