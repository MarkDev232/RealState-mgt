<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating inquiries...');

        $properties = Property::all();
        $clients = User::where('role', 'client')->get();

        if ($properties->isEmpty()) {
            $this->command->error('No properties found. Please run PropertySeeder first.');
            return;
        }

        // Create new inquiries
        Inquiry::factory()
            ->newInquiry()
            ->count(20)
            ->create();

        // Create contacted inquiries
        Inquiry::factory()
            ->contacted()
            ->count(15)
            ->create();

        // Create follow-up inquiries
        Inquiry::factory()
            ->followUp()
            ->count(10)
            ->create();

        // Create closed inquiries
        Inquiry::factory()
            ->closed()
            ->count(8)
            ->create();

        // Create inquiries from existing clients (using client data but not user_id)
        foreach ($clients->take(8) as $client) {
            $clientProperties = $properties->random(rand(1, 2));
            
            foreach ($clientProperties as $property) {
                Inquiry::factory()
                    ->forProperty($property)
                    ->state([
                        'name' => $client->name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                    ])
                    ->newInquiry()
                    ->create();
            }
        }

        // Create recent inquiries
        Inquiry::factory()
            ->recent()
            ->count(10)
            ->create();

        $totalInquiries = Inquiry::count();
        $newInquiries = Inquiry::where('status', 'new')->count();
        $followUpInquiries = Inquiry::where('status', 'follow_up')->count();

        $this->command->info("✅ Created {$totalInquiries} total inquiries");
        $this->command->info("✅ {$newInquiries} new inquiries");
        $this->command->info("✅ {$followUpInquiries} follow-up inquiries");
    }
}