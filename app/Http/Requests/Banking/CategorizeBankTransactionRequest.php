<?php

namespace App\Http\Requests\Banking;

use Illuminate\Foundation\Http\FormRequest;

class CategorizeBankTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internal_category' => ['required', 'string', 'max:120'],
            'source' => ['nullable', 'in:manual,rule'],
        ];
    }
}
