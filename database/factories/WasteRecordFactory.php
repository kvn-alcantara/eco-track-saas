<?php

namespace Database\Factories;

use App\Enums\WasteType;
use App\Models\Company;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WasteRecord>
 */
class WasteRecordFactory extends Factory
{
    protected $model = WasteRecord::class;

    public function definition(): array
    {
        $wasteType = $this->faker->randomElement(WasteType::cases());

        return [
            'company_id' => Company::factory(),
            'recorded_by_user_id' => User::factory(),
            'waste_type' => $wasteType,
            'quantity_kg' => $this->faker->randomFloat(2, 1, 1000),
            'co2e_kg' => $this->faker->randomFloat(2, 1, 2000),
            'occurred_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'audit_snapshot' => [
                'source' => 'factory',
            ],
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (): array => [
            'company_id' => $company->id,
        ]);
    }
}
