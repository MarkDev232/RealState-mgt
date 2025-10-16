<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        $statuses = ['new', 'contacted', 'follow_up', 'closed'];
        
        return [
            'property_id' => Property::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'message' => $this->faker->paragraphs(2, true),
            'status' => $this->faker->randomElement($statuses),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function newInquiry(): static
    {
        return $this->state([
            'status' => 'new',
        ]);
    }

    public function contacted(): static
    {
        return $this->state([
            'status' => 'contacted',
        ]);
    }

    public function followUp(): static
    {
        return $this->state([
            'status' => 'follow_up',
        ]);
    }

    public function closed(): static
    {
        return $this->state([
            'status' => 'closed',
        ]);
    }

    public function forProperty(Property $property): static
    {
        return $this->state([
            'property_id' => $property->id,
        ]);
    }

    public function recent(): static
    {
        return $this->state([
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state([
            'created_at' => $this->faker->dateTimeBetween('-6 months', '-3 months'),
        ]);
    }
}