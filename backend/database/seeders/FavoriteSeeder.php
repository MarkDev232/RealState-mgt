<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating favorites...');

        $clients = User::where('role', 'client')->get();
        $properties = Property::where('status', 'available')->get();

        if ($clients->isEmpty() || $properties->isEmpty()) {
            $this->command->error('No clients or properties found. Please run UserSeeder and PropertySeeder first.');
            return;
        }

        $favoritesCreated = 0;

        foreach ($clients as $client) {
            // Each client favorites 0-5 properties
            $favoriteCount = rand(0, 5);
            
            // Get random properties to favorite
            $propertiesToFavorite = $properties->random(min($favoriteCount, $properties->count()));
            
            foreach ($propertiesToFavorite as $property) {
                Favorite::factory()
                    ->forUser($client)
                    ->forProperty($property)
                    ->create();
                
                $favoritesCreated++;
            }
        }

        // Create some recent favorites
        Favorite::factory()
            ->recent()
            ->count(10)
            ->create();

        $totalFavorites = Favorite::count();

        $this->command->info("✅ Created {$totalFavorites} favorites total");
        $this->command->info("✅ {$favoritesCreated} favorites for existing clients");
        $this->command->info("✅ 10 recent favorites added");
    }
}