<?php

namespace App\Http\Requests\CRM;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', Rule::in(array_keys(Lead::sourceOptions()))],
            'status' => ['nullable', 'in:uncontacted,contacted,quoted,booked,converted,lost'],
            'next_follow_up' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
            'notes' => ['nullable', 'array'],
            'expected_value' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}
