<?php

namespace Database\Factories;

use App\Models\CarbonReport;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarbonReport>
 */
class CarbonReportFactory extends Factory
{
    protected $model = CarbonReport::class;

    public function definition(): array
    {
        $periodStart = $this->faker->dateTimeBetween('-90 days', '-30 days');
        $periodEnd = $this->faker->dateTimeBetween('-29 days', 'now');

        return [
            'company_id' => Company::factory(),
            'generated_by_user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_waste_kg' => $this->faker->randomFloat(2, 100, 5000),
            'total_emissions_kg' => $this->faker->randomFloat(2, 50, 2500),
            'status' => 'generated',
            'summary' => [
                'records' => $this->faker->numberBetween(1, 100),
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
