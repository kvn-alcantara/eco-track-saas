<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'auditable_type' => WasteRecord::class,
            'auditable_id' => 1,
            'action' => 'updated',
            'before_state' => ['quantity_kg' => 100],
            'after_state' => ['quantity_kg' => 200],
            'metadata' => [],
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (): array => [
            'company_id' => $company->id,
        ]);
    }
}
