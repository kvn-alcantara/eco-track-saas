<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_users_are_isolated_by_company_except_during_auth(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $userB = User::factory()->create(['company_id' => $companyB->id]);

        // When authenticated as userA, we should NOT see userB if we query users
        Sanctum::actingAs($userA);
        
        $this->assertCount(1, User::all());
        $this->assertEquals($userA->id, User::first()->id);
        
        // However, we MUST be able to find userB by token even if not logged in yet
        // This is what my fix enabled.
        auth()->forgetUser();
        
        $token = $userB->createToken('api')->plainTextToken;
        // In a real request, Sanctum would find userB. 
        // Here we simulate the query Sanctum does (simplistically)
        $foundUser = User::where('id', $userB->id)->first();
        $this->assertNotNull($foundUser, 'User should be findable even without auth context');
        $this->assertEquals($userB->id, $foundUser->id);
    }
}
