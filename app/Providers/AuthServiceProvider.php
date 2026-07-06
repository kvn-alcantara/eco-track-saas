<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\CarbonReport;
use App\Models\WasteRecord;
use App\Policies\AuditLogPolicy;
use App\Policies\CarbonReportPolicy;
use App\Policies\WasteRecordPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WasteRecord::class => WasteRecordPolicy::class,
        CarbonReport::class => CarbonReportPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
