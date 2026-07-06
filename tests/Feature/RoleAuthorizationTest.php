<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CarbonReport;
use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_colaborador_can_create_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->colaborador()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/waste-records', [
            'waste_type' => 'recyclable',
            'quantity_kg' => 50.0,
            'co2e_kg' => 25.0,
            'occurred_at' => now()->subDay(),
        ]);

        $response->assertCreated();
    }

    public function test_colaborador_cannot_update_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->colaborador()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/waste-records/{$record->id}", [
            'quantity_kg' => 999.0,
        ]);

        $response->assertForbidden();
    }

    public function test_colaborador_cannot_delete_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->colaborador()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/waste-records/{$record->id}");

        $response->assertForbidden();
    }

    public function test_colaborador_cannot_create_carbon_report(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->colaborador()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/carbon-reports', [
            'title' => 'Report',
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'total_waste_kg' => 100,
            'total_emissions_kg' => 50,
        ]);

        $response->assertForbidden();
    }

    public function test_colaborador_cannot_view_audit_logs(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->colaborador()->create(['company_id' => $company->id]);
        AuditLog::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertForbidden();
    }

    public function test_auditor_cannot_create_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->auditor()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/waste-records', [
            'waste_type' => 'recyclable',
            'quantity_kg' => 50.0,
            'co2e_kg' => 25.0,
            'occurred_at' => now()->subDay(),
        ]);

        $response->assertForbidden();
    }

    public function test_auditor_can_view_audit_logs(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->auditor()->create(['company_id' => $company->id]);
        AuditLog::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_auditor_can_approve_completed_report(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->auditor()->create(['company_id' => $company->id]);
        $report = CarbonReport::factory()->forCompany($company)->create(['status' => 'completed']);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/carbon-reports/{$report->id}/approve");

        $response->assertOk();
        $response->assertJsonPath('data.status', 'approved');
    }

    public function test_colaborador_cannot_approve_report(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->colaborador()->create(['company_id' => $company->id]);
        $report = CarbonReport::factory()->forCompany($company)->create(['status' => 'completed']);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/carbon-reports/{$report->id}/approve");

        $response->assertForbidden();
    }

    public function test_manager_retains_full_waste_access(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/waste-records', [
            'waste_type' => 'recyclable',
            'quantity_kg' => 50.0,
            'co2e_kg' => 25.0,
            'occurred_at' => now()->subDay(),
        ])->assertCreated();

        $this->patchJson("/api/v1/waste-records/{$record->id}", [
            'quantity_kg' => 200.0,
        ])->assertOk();

        $this->deleteJson("/api/v1/waste-records/{$record->id}")->assertNoContent();
    }
}
