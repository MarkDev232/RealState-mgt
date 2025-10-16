<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add columns that don't exist
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('role');
            }
            
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('bio');
            }
            
            if (!Schema::hasColumn('users', 'license_number')) {
                $table->string('license_number')->nullable()->after('company_name');
            }
            
            if (!Schema::hasColumn('users', 'experience_years')) {
                $table->integer('experience_years')->nullable()->after('license_number');
            }
            
            if (!Schema::hasColumn('users', 'specialization')) {
                $table->string('specialization')->nullable()->after('experience_years');
            }
            
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'company_name',
                'license_number',
                'experience_years',
                'specialization',
                'last_login_at'
            ]);
        });
    }
};