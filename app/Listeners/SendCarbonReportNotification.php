<?php

namespace App\Listeners;

use App\Events\CarbonReportGenerated;
use Illuminate\Support\Facades\Log;

class SendCarbonReportNotification
{
    public function handle(CarbonReportGenerated $event): void
    {
        Log::info(sprintf(
            'Simulating email notification sent for carbon report: %d (Title: %s, Company ID: %d)',
            $event->report->id,
            $event->report->title,
            $event->report->company_id
        ));
    }
}
