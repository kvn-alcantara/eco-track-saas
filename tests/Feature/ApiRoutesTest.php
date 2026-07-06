<?php

namespace Tests\Feature;

use App\Models\CarbonReport;
use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_waste_records_api(): void
    {
        $response = $this->getJson('/api/v1/waste-records');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_waste_records(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $records = WasteRecord::factory()->count(3)->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/waste-records');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'company_id',
                    'waste_type',
                    'quantity_kg',
                    'co2e_kg',
                    'occurred_at',
                    'notes',
                ]
            ]
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_create_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/waste-records', [
            'waste_type' => 'recyclable',
            'quantity_kg' => 150.5,
            'co2e_kg' => 75.25,
            'occurred_at' => now()->subDay(),
            'notes' => 'Test waste record creation',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'company_id',
                'waste_type',
                'quantity_kg',
                'co2e_kg',
                'occurred_at',
                'notes',
            ]
        ]);

        $this->assertDatabaseHas('waste_records', [
            'company_id' => $company->id,
            'waste_type' => 'recyclable',
            'quantity_kg' => 150.5,
        ]);
    }

    public function test_authenticated_user_can_show_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/waste-records/{$record->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $record->id,
            'waste_type' => $record->waste_type,
        ]);
    }

    public function test_authenticated_user_can_update_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/waste-records/{$record->id}", [
            'quantity_kg' => 200.0,
            'notes' => 'Updated notes',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('waste_records', [
            'id' => $record->id,
            'quantity_kg' => 200.0,
            'notes' => 'Updated notes',
        ]);
    }

    public function test_authenticated_user_can_delete_waste_record(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/waste-records/{$record->id}");

        $response->assertNoContent();
        $this->assertModelMissing($record);
    }

    public function test_unauthenticated_user_cannot_access_carbon_reports_api(): void
    {
        $response = $this->getJson('/api/v1/carbon-reports');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_carbon_reports(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $reports = CarbonReport::factory()->count(2)->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/carbon-reports');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'company_id',
                    'title',
                    'period_start',
                    'period_end',
                    'total_waste_kg',
                    'total_emissions_kg',
                    'status',
                ]
            ]
        ]);
        $response->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_create_carbon_report(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/carbon-reports', [
            'title' => 'Q3 2026 Carbon Report',
            'period_start' => now()->subMonths(3),
            'period_end' => now(),
            'total_waste_kg' => 5000.0,
            'total_emissions_kg' => 2500.0,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'company_id',
                'title',
                'period_start',
                'period_end',
                'total_waste_kg',
                'total_emissions_kg',
                'status',
            ]
        ]);

        $this->assertDatabaseHas('carbon_reports', [
            'company_id' => $company->id,
            'title' => 'Q3 2026 Carbon Report',
        ]);
    }

    public function test_authenticated_user_can_show_carbon_report(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $report = CarbonReport::factory()->forCompany($company)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/carbon-reports/{$report->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $report->id,
            'title' => $report->title,
        ]);
    }
}
