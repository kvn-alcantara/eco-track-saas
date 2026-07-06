<?php

namespace Tests\Feature;

use App\Models\CarbonReport;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CarbonReportsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_carbon_reports(): void
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
                    'title',
                    'period_start',
                    'period_end',
                    'total_waste_kg',
                    'total_emissions_kg',
                    'status',
                ]
            ]
        ]);
        $response->assertJsonMissingPath('data.0.company_id');
        $response->assertJsonMissingPath('data.0.generated_by_user_id');
        $response->assertJsonMissingPath('data.0.summary');
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
                'title',
                'period_start',
                'period_end',
                'total_waste_kg',
                'total_emissions_kg',
                'status',
            ]
        ]);
        $response->assertJsonMissingPath('data.company_id');
        $response->assertJsonMissingPath('data.generated_by_user_id');
        $response->assertJsonMissingPath('data.summary');

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
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'period_start',
                'period_end',
                'total_waste_kg',
                'total_emissions_kg',
                'status',
            ]
        ]);
        $response->assertJsonMissingPath('data.company_id');
        $response->assertJsonMissingPath('data.generated_by_user_id');
        $response->assertJsonMissingPath('data.summary');
    }

    public function test_user_cannot_access_other_company_carbon_report(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $report = CarbonReport::factory()->forCompany($company2)->create();

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/carbon-reports/{$report->id}");

        $response->assertNotFound();
    }
}
