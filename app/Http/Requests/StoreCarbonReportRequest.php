<?php

namespace App\Http\Requests;

use App\Models\CarbonReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreCarbonReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CarbonReport::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'total_waste_kg' => ['required', 'numeric', 'min:0'],
            'total_emissions_kg' => ['required', 'numeric', 'min:0'],
        ];
    }
}
