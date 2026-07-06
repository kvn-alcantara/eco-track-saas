<?php

namespace App\Http\Requests;

use App\Enums\WasteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWasteRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waste_type' => ['required', Rule::enum(WasteType::class)],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'co2e_kg' => ['required', 'numeric', 'min:0'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
