<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'uuid', 'exists:contacts,id'],
            'service_id' => ['nullable', 'uuid', 'exists:services,id'],
            'scheduled_date' => ['nullable', 'date'],
            'assigned_contractor_id' => ['nullable', 'uuid', 'exists:contacts,id'],
            'special_instructions' => ['nullable', 'string'],
            'notes' => ['nullable', 'array'],
        ];
    }
}
