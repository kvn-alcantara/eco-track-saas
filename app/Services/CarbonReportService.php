<?php

namespace App\Services;

use App\Jobs\GenerateCarbonReportJob;
use App\Models\CarbonReport;
use App\Models\Company;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CarbonReportService
{
    public function listByCompany(Company $company, int $perPage = 10): LengthAwarePaginator
    {
        return CarbonReport::where('company_id', $company->id)
            ->latest('period_end')
            ->paginate($perPage);
    }

    public function create(Company $company, User $generatedBy, array $data): CarbonReport
    {
        $periodStart = Carbon::parse($data['period_start'])->startOfDay();
        $periodEnd = Carbon::parse($data['period_end'])->endOfDay();

        return CarbonReport::create([
            'company_id' => $company->id,
            'generated_by_user_id' => $generatedBy->id,
            'title' => $data['title'],
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_waste_kg' => $data['total_waste_kg'],
            'total_emissions_kg' => $data['total_emissions_kg'],
            'status' => 'generated',
            'summary' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function generate(Company $company, User $generatedBy, array $data): CarbonReport
    {
        $periodStart = Carbon::parse($data['period_start'])->startOfDay();
        $periodEnd = Carbon::parse($data['period_end'])->endOfDay();

        $report = CarbonReport::create([
            'company_id' => $company->id,
            'generated_by_user_id' => $generatedBy->id,
            'title' => $data['title'],
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_waste_kg' => 0.0,
            'total_emissions_kg' => 0.0,
            'status' => 'processing',
            'summary' => [
                'requested_at' => now()->toIso8601String(),
            ],
        ]);

        GenerateCarbonReportJob::dispatch($report);

        return $report;
    }

    public function approve(CarbonReport $report, User $approver): CarbonReport
    {
        if ($report->status === 'approved') {
            throw ValidationException::withMessages([
                'status' => ['Este relatório já foi aprovado.'],
            ]);
        }

        if (! in_array($report->status, ['completed', 'generated'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Somente relatórios concluídos ou gerados podem ser aprovados.'],
            ]);
        }

        $report->update([
            'status' => 'approved',
            'summary' => array_merge($report->summary ?? [], [
                'approved_at' => now()->toIso8601String(),
                'approved_by_user_id' => $approver->id,
            ]),
        ]);

        return $report->fresh();
    }
}
