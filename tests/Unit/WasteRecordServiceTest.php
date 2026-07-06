<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\WasteRecord;
use App\Services\AuditLogService;
use App\Services\WasteRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WasteRecordServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_rolls_back_when_audit_log_write_fails(): void
    {
        $company = Company::factory()->create();
        $actor = User::factory()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create([
            'quantity_kg' => 120.00,
            'notes' => 'Original notes',
        ]);

        $this->app->instance(AuditLogService::class, new class extends AuditLogService
        {
            public function recordWasteRecordChange(
                WasteRecord $wasteRecord,
                ?User $actor,
                string $action,
                array $beforeState,
                array $afterState,
                array $metadata = []
            ): AuditLog {
                throw new RuntimeException('Audit log failure');
            }
        });

        $service = $this->app->make(WasteRecordService::class);

        try {
            $service->update($record, [
                'quantity_kg' => 250.00,
                'notes' => 'Updated notes',
            ], $actor);

            $this->fail('Expected audit log failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit log failure', $exception->getMessage());
        }

        $record->refresh();

        $this->assertSame('120.00', $record->quantity_kg);
        $this->assertSame('Original notes', $record->notes);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_delete_rolls_back_when_audit_log_write_fails(): void
    {
        $company = Company::factory()->create();
        $actor = User::factory()->create(['company_id' => $company->id]);
        $record = WasteRecord::factory()->forCompany($company)->create();

        $this->app->instance(AuditLogService::class, new class extends AuditLogService
        {
            public function recordWasteRecordChange(
                WasteRecord $wasteRecord,
                ?User $actor,
                string $action,
                array $beforeState,
                array $afterState,
                array $metadata = []
            ): AuditLog {
                throw new RuntimeException('Audit log failure');
            }
        });

        $service = $this->app->make(WasteRecordService::class);

        try {
            $service->delete($record, $actor);

            $this->fail('Expected audit log failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit log failure', $exception->getMessage());
        }

        $this->assertDatabaseHas('waste_records', [
            'id' => $record->id,
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
