<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Seeder;

class PropertyImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating property images...');

        $properties = Property::all();

        if ($properties->isEmpty()) {
            $this->command->error('No properties found. Please run PropertySeeder first.');
            return;
        }

        $totalImages = 0;

        foreach ($properties as $property) {
            // Each property gets 3-8 images
            $imageCount = rand(3, 8);
            
            // Create one primary image
            PropertyImage::factory()
                ->primary()
                ->forProperty($property)
                ->create();

            // Create additional images
            PropertyImage::factory()
                ->count($imageCount - 1)
                ->forProperty($property)
                ->create();

            $totalImages += $imageCount;
        }

        $this->command->info("✅ Created {$totalImages} property images");
        $this->command->info("✅ Added primary image for each property");
    }
}