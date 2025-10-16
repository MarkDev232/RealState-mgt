<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement(['client', 'agent', 'admin']),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'avatar' => $this->faker->imageUrl(100, 100, 'people'),
            'bio' => $this->faker->paragraph(),
            'company_name' => $this->faker->company(),
            'license_number' => $this->faker->bothify('LIC###??'),
            'experience_years' => $this->faker->numberBetween(1, 30),
            'specialization' => $this->faker->randomElement(['Residential', 'Commercial', 'Luxury', 'Rental']),
            'is_active' => true,
            'last_login_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'remember_token' => Str::random(10),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function client(): static
    {
        return $this->state([
            'role' => 'client',
            'company_name' => null,
            'license_number' => null,
            'experience_years' => null,
            'specialization' => null,
        ]);
    }

    public function agent(): static
    {
        return $this->state([
            'role' => 'agent',
            'company_name' => $this->faker->company(),
            'license_number' => $this->faker->bothify('LIC###??'),
            'experience_years' => $this->faker->numberBetween(1, 30),
            'specialization' => $this->faker->randomElement(['Residential', 'Commercial', 'Luxury', 'Rental']),
        ]);
    }

    public function admin(): static
    {
        return $this->state([
            'role' => 'admin',
            'company_name' => null,
            'license_number' => null,
            'experience_years' => null,
            'specialization' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    public function unverified(): static
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }
}