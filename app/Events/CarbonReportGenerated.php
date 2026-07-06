<?php

namespace App\Events;

use App\Models\CarbonReport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CarbonReportGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CarbonReport $report
    ) {}
}
