<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyImageFactory extends Factory
{
    protected $model = PropertyImage::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'image_path' => $this->faker->imageUrl(800, 600, 'house', true, 'property'),
            'is_primary' => false,
            'order' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function primary(): static
    {
        return $this->state([
            'is_primary' => true,
            'order' => 0,
        ]);
    }

    public function forProperty(Property $property): static
    {
        return $this->state([
            'property_id' => $property->id,
        ]);
    }

    public function withOrder(int $order): static
    {
        return $this->state([
            'order' => $order,
        ]);
    }
}