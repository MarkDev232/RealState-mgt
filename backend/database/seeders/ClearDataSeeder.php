<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Clearing existing data...');

        $tables = [
            'inquiries',
            'appointments',
            'favorites',
            'property_images', // Clear this before properties
            'properties',      // Clear properties before users
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->command->info("✅ Cleared {$table} table");
        }

        $this->command->info('All tables cleared successfully!');
    }
}