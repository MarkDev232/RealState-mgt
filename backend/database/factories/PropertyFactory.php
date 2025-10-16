<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $propertyTypes = ['house', 'apartment', 'condo', 'townhouse', 'land', 'commercial'];
        $statuses = ['available', 'sold', 'pending', 'rented'];
        $listingTypes = ['sale', 'rent'];
        
        return [
            'agent_id' => User::factory()->agent(),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'country' => 'USA',
            'price' => $this->faker->numberBetween(50000, 2000000),
            'bedrooms' => $this->faker->numberBetween(1, 6),
            'bathrooms' => $this->faker->numberBetween(1, 4),
            'square_feet' => $this->faker->numberBetween(500, 5000),
            'lot_size' => $this->faker->numberBetween(1000, 10000),
            'property_type' => $this->faker->randomElement($propertyTypes),
            'status' => $this->faker->randomElement($statuses),
            'listing_type' => $this->faker->randomElement($listingTypes),
            'year_built' => $this->faker->numberBetween(1950, 2023),
            'amenities' => json_encode($this->generateAmenities()),
            'images' => json_encode($this->generateImages()),
            'featured' => $this->faker->boolean(20),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Generate random amenities
     */
    protected function generateAmenities(): array
    {
        $allAmenities = [
            'parking', 'pool', 'garden', 'fireplace', 'basement', 
            'garage', 'air_conditioning', 'heating', 'balcony', 
            'fitness_center', 'security', 'elevator', 'pet_friendly'
        ];
        
        return $this->faker->randomElements($allAmenities, $this->faker->numberBetween(3, 8));
    }

    /**
     * Generate random image URLs
     */
    protected function generateImages(): array
    {
        $images = [];
        $imageCount = $this->faker->numberBetween(3, 8);
        
        for ($i = 0; $i < $imageCount; $i++) {
            $images[] = [
                'url' => $this->faker->imageUrl(800, 600, 'house', true, 'property'),
                'caption' => $this->faker->optional()->sentence(),
                'is_primary' => $i === 0
            ];
        }
        
        return $images;
    }

    public function available(): static
    {
        return $this->state([
            'status' => 'available',
        ]);
    }

    public function sold(): static
    {
        return $this->state([
            'status' => 'sold',
        ]);
    }

    public function rented(): static
    {
        return $this->state([
            'status' => 'rented',
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    public function featured(): static
    {
        return $this->state([
            'featured' => true,
        ]);
    }

    public function forSale(): static
    {
        return $this->state([
            'listing_type' => 'sale',
        ]);
    }

    public function forRent(): static
    {
        return $this->state([
            'listing_type' => 'rent',
            'price' => $this->faker->numberBetween(1000, 5000), // Lower prices for rent
        ]);
    }

    public function withSpecificAgent(User $agent): static
    {
        return $this->state([
            'agent_id' => $agent->id,
        ]);
    }

    public function apartment(): static
    {
        return $this->state([
            'property_type' => 'apartment',
            'square_feet' => $this->faker->numberBetween(500, 1500),
            'lot_size' => null,
        ]);
    }

    public function house(): static
    {
        return $this->state([
            'property_type' => 'house',
            'square_feet' => $this->faker->numberBetween(1000, 4000),
            'lot_size' => $this->faker->numberBetween(2000, 10000),
        ]);
    }

    public function commercial(): static
    {
        return $this->state([
            'property_type' => 'commercial',
            'bedrooms' => null,
            'bathrooms' => null,
            'square_feet' => $this->faker->numberBetween(2000, 10000),
        ]);
    }
}