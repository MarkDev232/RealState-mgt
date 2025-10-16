<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $appointmentDate = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        
        return [
            'user_id' => User::factory()->client(),
            'property_id' => Property::factory(),
            'agent_id' => function (array $attributes) {
                return Property::find($attributes['property_id'])->agent_id;
            },
            'appointment_date' => $appointmentDate,
            'status' => $this->faker->randomElement($statuses),
            'notes' => $this->faker->optional(0.5)->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    public function confirmed(): static
    {
        return $this->state([
            'status' => 'confirmed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
            'notes' => $this->faker->optional(0.8)->sentence() . ' (Cancelled)',
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'appointment_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state([
            'appointment_date' => $this->faker->dateTimeBetween('+1 day', '+14 days'),
            'status' => $this->faker->randomElement(['pending', 'confirmed']),
        ]);
    }

    public function past(): static
    {
        return $this->state([
            'appointment_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'status' => $this->faker->randomElement(['completed', 'cancelled']),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }

    public function forProperty(Property $property): static
    {
        return $this->state([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
        ]);
    }

    public function withNotes(): static
    {
        return $this->state([
            'notes' => $this->faker->paragraph(),
        ]);
    }

    public function scheduledBetween($start, $end): static
    {
        return $this->state([
            'appointment_date' => $this->faker->dateTimeBetween($start, $end),
        ]);
    }
}