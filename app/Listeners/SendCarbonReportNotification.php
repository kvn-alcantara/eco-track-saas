<?php

namespace App\Listeners;

use App\Events\CarbonReportGenerated;
use App\Mail\CarbonReportGeneratedMail;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Mail;

class SendCarbonReportNotification
{
    public function handle(CarbonReportGenerated $event): void
    {
        $report = $event->report;
        
        $report->load('company');
        $company = $report->company;

        if ($company) {
            $users = $company->users()
                ->withoutGlobalScope(CompanyScope::class)
                ->get();

            foreach ($users as $user) {
                Mail::to($user->email)->send(new CarbonReportGeneratedMail($report));
            }
        }
    }
}
