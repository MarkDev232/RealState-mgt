<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks for better performance
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        $this->call(ClearDataSeeder::class);

        // Seed users first (they're dependencies for other models)
        $this->call(UserSeeder::class);

        // Seed properties and related data
        $this->call(PropertySeeder::class);
        $this->call(PropertyImageSeeder::class);

        // Seed relationship data
        $this->call(FavoriteSeeder::class);
        $this->call(AppointmentSeeder::class);
        $this->call(InquirySeeder::class);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('👤 Default admin login: admin@example.com / password');
        $this->command->info('👨‍💼 Default agent login: agent@example.com / password');
        $this->command->info('👤 Default client login: client@example.com / password');
    }
}