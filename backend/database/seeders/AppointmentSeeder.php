<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating appointments...');

        $clients = User::where('role', 'client')->get();
        $properties = Property::all();

        if ($clients->isEmpty() || $properties->isEmpty()) {
            $this->command->error('No clients or properties found. Please run UserSeeder and PropertySeeder first.');
            return;
        }

        // Create upcoming appointments
        Appointment::factory()
            ->upcoming()
            ->count(15)
            ->create();

        // Create past appointments (completed and cancelled)
        Appointment::factory()
            ->completed()
            ->count(10)
            ->create();

        Appointment::factory()
            ->cancelled()
            ->count(5)
            ->create();

        // Create some pending appointments
        Appointment::factory()
            ->pending()
            ->count(8)
            ->create();

        // Create some confirmed appointments
        Appointment::factory()
            ->confirmed()
            ->count(12)
            ->create();

        // Create appointments for specific clients and properties
        foreach ($clients->take(5) as $client) {
            $clientProperties = $properties->random(rand(1, 3));
            
            foreach ($clientProperties as $property) {
                Appointment::factory()
                    ->forUser($client)
                    ->forProperty($property)
                    ->upcoming()
                    ->create();
            }
        }

        $totalAppointments = Appointment::count();
        $upcomingAppointments = Appointment::whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>', now())
            ->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();

        $this->command->info("✅ Created {$totalAppointments} total appointments");
        $this->command->info("✅ {$upcomingAppointments} upcoming appointments");
        $this->command->info("✅ {$completedAppointments} completed appointments");
    }
}