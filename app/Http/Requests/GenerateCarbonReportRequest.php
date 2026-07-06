<?php

namespace App\Http\Requests;

use App\Models\CarbonReport;
use Illuminate\Foundation\Http\FormRequest;

class GenerateCarbonReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('generate', CarbonReport::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ];
    }
}
