<?php

namespace App\Jobs;

use App\Events\CarbonReportGenerated;
use App\Models\CarbonReport;
use App\Models\WasteRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCarbonReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CarbonReport $report
    ) {}

    public function handle(): void
    {
        // Bind the tenant context to ensure Eloquent queries are correctly scoped
        app()->instance('currentCompanyId', $this->report->company_id);

        $totalWaste = 0.0;
        $totalEmissions = 0.0;

        $periodStart = $this->report->period_start->copy()->startOfDay();
        $periodEnd = $this->report->period_end->copy()->endOfDay();

        // Query waste records in chunks of 100 to optimize memory usage
        WasteRecord::where('company_id', $this->report->company_id)
            ->whereBetween('occurred_at', [$periodStart, $periodEnd])
            ->chunk(100, function ($records) use (&$totalWaste, &$totalEmissions): void {
                foreach ($records as $record) {
                    $totalWaste += (float) $record->quantity_kg;
                    $totalEmissions += (float) $record->co2e_kg;
                }
            });

        $this->report->update([
            'total_waste_kg' => $totalWaste,
            'total_emissions_kg' => $totalEmissions,
            'status' => 'completed',
            'summary' => array_merge($this->report->summary ?? [], [
                'completed_at' => now()->toIso8601String(),
                'records_processed' => WasteRecord::where('company_id', $this->report->company_id)
                    ->whereBetween('occurred_at', [$periodStart, $periodEnd])
                    ->count(),
            ]),
        ]);

        event(new CarbonReportGenerated($this->report));
    }
}
