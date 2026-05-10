<?php

namespace App\Http\Requests\Banking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'uuid', 'exists:bank_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'uuid', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transfer_date' => ['required', 'date'],
            'status' => ['nullable', 'in:pending,completed,failed,cancelled'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
