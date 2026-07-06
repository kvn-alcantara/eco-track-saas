<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWasteRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waste_type' => ['required', 'string', 'max:80'],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'co2e_kg' => ['required', 'numeric', 'min:0'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
