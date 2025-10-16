<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating users...');

        // Create specific test users
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1-555-0101',
            'bio' => 'System administrator with full access to all features.',
        ]);

        $agent = User::factory()->create([
            'name' => 'John Realty',
            'email' => 'agent@example.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'phone' => '+1-555-0102',
            'company_name' => 'Premium Realty Group',
            'license_number' => 'AG123456',
            'experience_years' => 8,
            'specialization' => 'Luxury',
            'bio' => 'Specializing in luxury properties with over 8 years of experience.',
        ]);

        $client = User::factory()->create([
            'name' => 'Sarah Client',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'phone' => '+1-555-0103',
            'bio' => 'Looking for a new family home in the suburbs.',
        ]);

        // Create additional agents
        User::factory()->agent()->count(5)->create();

        // Create additional clients
        User::factory()->client()->count(15)->create();

        // Create a few inactive users
        User::factory()->inactive()->count(2)->create();

        $this->command->info("✅ Created {$admin->name} (Admin)");
        $this->command->info("✅ Created {$agent->name} (Agent)");
        $this->command->info("✅ Created {$client->name} (Client)");
        $this->command->info('✅ Created 5 additional agents');
        $this->command->info('✅ Created 15 additional clients');
        $this->command->info('✅ Created 2 inactive users');
    }
}