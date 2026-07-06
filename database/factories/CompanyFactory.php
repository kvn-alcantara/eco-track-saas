<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyName = $this->faker->company();

        return [
            'name' => $companyName,
            'slug' => Str::slug($companyName).'-'.Str::lower(Str::random(5)),
            'tax_id' => $this->faker->numerify('##.###.###/####-##'),
            'industry' => $this->faker->randomElement(['Manufacturing', 'Logistics', 'Retail', 'Energy']),
        ];
    }
}
