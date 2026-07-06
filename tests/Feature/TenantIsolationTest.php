<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    public function test_company_only_sees_its_own_waste_records(): void
    {
        $companyA = Company::factory()->create(['name' => 'Alpha Meio Ambiente', 'slug' => 'alpha-meio-ambiente']);
        $companyB = Company::factory()->create(['name' => 'Beta Indústria', 'slug' => 'beta-industria']);

        $userA = User::factory()->create(['company_id' => $companyA->id, 'email' => 'alpha@example.com']);
        $userB = User::factory()->create(['company_id' => $companyB->id, 'email' => 'beta@example.com']);

        WasteRecord::factory()->count(2)->forCompany($companyA)->create(['recorded_by_user_id' => $userA->id]);
        $foreignRecord = WasteRecord::factory()->forCompany($companyB)->create(['recorded_by_user_id' => $userB->id]);

        Sanctum::actingAs($userA);

        $indexResponse = $this->getJson('/api/v1/waste-records');

        $indexResponse->assertOk();
        $indexResponse->assertJsonCount(2, 'data');
        $indexResponse->assertJsonMissing(['id' => $foreignRecord->id]);

        $this->getJson('/api/v1/waste-records/'.$foreignRecord->id)->assertNotFound();
    }

    public function test_company_only_sees_its_own_carbon_reports(): void
    {
        $companyA = Company::factory()->create(['name' => 'Alpha Meio Ambiente', 'slug' => 'alpha-meio-ambiente']);
        $companyB = Company::factory()->create(['name' => 'Beta Indústria', 'slug' => 'beta-industria']);

        $userA = User::factory()->create(['company_id' => $companyA->id, 'email' => 'alpha-report@example.com']);
        $userB = User::factory()->create(['company_id' => $companyB->id, 'email' => 'beta-report@example.com']);

        $ownReport = \App\Models\CarbonReport::factory()->forCompany($companyA)->create(['generated_by_user_id' => $userA->id]);
        $foreignReport = \App\Models\CarbonReport::factory()->forCompany($companyB)->create(['generated_by_user_id' => $userB->id]);

        Sanctum::actingAs($userA);

        $indexResponse = $this->getJson('/api/v1/carbon-reports');

        $indexResponse->assertOk();
        $indexResponse->assertJsonCount(1, 'data');
        $indexResponse->assertJsonFragment(['id' => $ownReport->id]);
        $indexResponse->assertJsonMissing(['id' => $foreignReport->id]);
    }
}
