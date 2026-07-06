<?php

namespace App\Http\Requests;

use App\Enums\WasteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWasteRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waste_type' => ['sometimes', Rule::enum(WasteType::class)],
            'quantity_kg' => ['sometimes', 'numeric', 'min:0.01'],
            'co2e_kg' => ['sometimes', 'numeric', 'min:0'],
            'occurred_at' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
