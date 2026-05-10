<?php

namespace App\Http\Requests\Banking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'uuid', 'exists:bank_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transaction_type' => ['required', 'in:deposit,withdrawal,transfer_in,transfer_out,fee,interest,other'],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:120'],
            'external_category' => ['nullable', 'string', 'max:120'],
            'internal_category' => ['nullable', 'string', 'max:120'],
            'category_source' => ['nullable', 'in:mercury,manual,rule'],
            'status' => ['nullable', 'in:pending,categorized,reconciled'],
            'journal_entry_id' => ['nullable', 'uuid', 'exists:journal_entries,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
