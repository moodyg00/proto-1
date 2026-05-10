<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'uuid', 'exists:organizations,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'subtotal' => ['required', 'numeric', 'gte:0'],
            'tax_amount' => ['nullable', 'numeric', 'gte:0'],
            'total_amount' => ['required', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
