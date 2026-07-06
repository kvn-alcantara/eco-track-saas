<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WasteRecordsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_waste_records(): void
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
                    'waste_type',
                    'quantity_kg',
                    'co2e_kg',
                    'occurred_at',
                    'notes',
                ]
            ]
        ]);
        $response->assertJsonMissingPath('data.0.company_id');
        $response->assertJsonMissingPath('data.0.recorded_by_user_id');
        $response->assertJsonMissingPath('data.0.audit_snapshot');
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
                'waste_type',
                'quantity_kg',
                'co2e_kg',
                'occurred_at',
                'notes',
            ]
        ]);
        $response->assertJsonMissingPath('data.company_id');
        $response->assertJsonMissingPath('data.recorded_by_user_id');
        $response->assertJsonMissingPath('data.audit_snapshot');

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
        $response->assertJsonStructure([
            'data' => [
                'id',
                'waste_type',
                'quantity_kg',
                'co2e_kg',
                'occurred_at',
                'notes',
            ]
        ]);
        $response->assertJsonMissingPath('data.company_id');
        $response->assertJsonMissingPath('data.recorded_by_user_id');
        $response->assertJsonMissingPath('data.audit_snapshot');
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
        $response->assertJsonStructure([
            'data' => [
                'id',
                'waste_type',
                'quantity_kg',
                'co2e_kg',
                'occurred_at',
                'notes',
            ]
        ]);
        $this->assertDatabaseHas('waste_records', [
            'id' => $record->id,
            'quantity_kg' => 200.0,
            'notes' => 'Updated notes',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'auditable_type' => WasteRecord::class,
            'auditable_id' => $record->id,
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
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'deleted',
            'auditable_type' => WasteRecord::class,
            'auditable_id' => $record->id,
        ]);
    }

    public function test_user_cannot_access_other_company_waste_record(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $record = WasteRecord::factory()->forCompany($company2)->create();

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/waste-records/{$record->id}");

        $response->assertNotFound();
    }

    public function test_user_cannot_update_other_company_waste_record(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $record = WasteRecord::factory()->forCompany($company2)->create();

        Sanctum::actingAs($user1);

        $response = $this->patchJson("/api/v1/waste-records/{$record->id}", [
            'quantity_kg' => 999.0,
        ]);

        $response->assertNotFound();
    }

    public function test_user_cannot_delete_other_company_waste_record(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $record = WasteRecord::factory()->forCompany($company2)->create();

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/v1/waste-records/{$record->id}");

        $response->assertNotFound();
    }
}
