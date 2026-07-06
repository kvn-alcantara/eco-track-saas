<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'manager',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => [
            'role' => 'admin',
        ]);
    }

    public function colaborador(): static
    {
        return $this->state(fn (): array => [
            'role' => 'colaborador',
        ]);
    }

    public function auditor(): static
    {
        return $this->state(fn (): array => [
            'role' => 'auditor',
        ]);
    }
}
