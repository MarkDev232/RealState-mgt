<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating properties...');

        // Get all agents
        $agents = User::where('role', 'agent')->get();

        if ($agents->isEmpty()) {
            $this->command->error('No agents found. Please run UserSeeder first.');
            return;
        }

        // Create properties for each agent
        foreach ($agents as $agent) {
            $propertiesCount = rand(2, 8); // Each agent has 2-8 properties
            
            Property::factory()
                ->count($propertiesCount)
                ->withSpecificAgent($agent)
                ->create();
        }

        // Create some featured properties
        Property::factory()
            ->featured()
            ->count(5)
            ->create();

        // Create some sold properties
        Property::factory()
            ->sold()
            ->count(3)
            ->create();

        // Create some rented properties
        Property::factory()
            ->rented()
            ->count(4)
            ->create();

        // Create some pending properties
        Property::factory()
            ->pending()
            ->count(2)
            ->create();

        // Create some properties for rent
        Property::factory()
            ->forRent()
            ->count(8)
            ->create();

        $totalProperties = Property::count();
        $availableProperties = Property::where('status', 'available')->count();
        $featuredProperties = Property::where('featured', true)->count();
        $forRentProperties = Property::where('listing_type', 'rent')->count();

        $this->command->info("✅ Created {$totalProperties} total properties");
        $this->command->info("✅ {$availableProperties} properties available");
        $this->command->info("✅ {$featuredProperties} featured properties");
        $this->command->info("✅ {$forRentProperties} rental properties");
    }
}