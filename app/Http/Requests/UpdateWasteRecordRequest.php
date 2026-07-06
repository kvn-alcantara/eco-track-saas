<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWasteRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waste_type' => ['sometimes', 'string', 'max:80'],
            'quantity_kg' => ['sometimes', 'numeric', 'min:0.01'],
            'co2e_kg' => ['sometimes', 'numeric', 'min:0'],
            'occurred_at' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
