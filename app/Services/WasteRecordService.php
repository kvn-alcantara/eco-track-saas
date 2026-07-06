<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Pagination\LengthAwarePaginator;

class WasteRecordService
{
    public function listByCompany(Company $company, int $perPage = 15): LengthAwarePaginator
    {
        return WasteRecord::where('company_id', $company->id)
            ->latest('occurred_at')
            ->paginate($perPage);
    }

    public function create(Company $company, User $recordedBy, array $data): WasteRecord
    {
        return WasteRecord::create([
            'company_id' => $company->id,
            'recorded_by_user_id' => $recordedBy->id,
            'waste_type' => $data['waste_type'],
            'quantity_kg' => $data['quantity_kg'],
            'co2e_kg' => $data['co2e_kg'],
            'occurred_at' => $data['occurred_at'],
            'notes' => $data['notes'] ?? null,
            'audit_snapshot' => [
                'source' => $data['source'] ?? 'api',
                'submitted_by' => $recordedBy->only(['id', 'name', 'email']),
            ],
        ]);
    }

    public function update(WasteRecord $wasteRecord, array $data): WasteRecord
    {
        $wasteRecord->update($data);
        return $wasteRecord->fresh();
    }

    public function delete(WasteRecord $wasteRecord): void
    {
        $wasteRecord->delete();
    }
}
