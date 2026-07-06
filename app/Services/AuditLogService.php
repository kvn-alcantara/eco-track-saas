<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\WasteRecord;

class AuditLogService
{
    public function recordWasteRecordChange(
        WasteRecord $wasteRecord,
        ?User $actor,
        string $action,
        array $beforeState,
        array $afterState,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'company_id' => $wasteRecord->company_id,
            'user_id' => $actor?->id,
            'auditable_type' => WasteRecord::class,
            'auditable_id' => $wasteRecord->id,
            'action' => $action,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'metadata' => $metadata,
        ]);
    }
}
