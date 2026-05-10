<?php

namespace App\Http\Requests\Banking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'uuid', 'exists:bank_accounts,id'],
            'statement_date' => ['required', 'date'],
            'statement_balance' => ['required', 'numeric'],
            'book_balance' => ['nullable', 'numeric'],
            'difference' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
