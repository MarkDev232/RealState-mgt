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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('country')->default('USA');
            $table->decimal('price', 10, 2);
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('square_feet')->nullable();
            $table->integer('lot_size')->nullable();
            $table->enum('property_type', ['house', 'apartment', 'condo', 'townhouse', 'land', 'commercial']);
            $table->enum('status', ['available', 'sold', 'pending', 'rented'])->default('available');
            $table->enum('listing_type', ['sale', 'rent']);
            $table->integer('year_built')->nullable();
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
