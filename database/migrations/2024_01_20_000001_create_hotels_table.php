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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            
            // Hotel Provider Relationship
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            
            // Basic Hotel Information
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['luxury', 'boutique', 'business', 'resort', 'budget', 'apartment'])->default('budget');
            $table->tinyInteger('star_rating')->default(3)->comment('1-5 star rating');
            
            // Location Information
            $table->text('address');
            $table->string('city');
            $table->string('country');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Contact Information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // Hotel Policies & Information
            $table->time('check_in_time')->default('15:00');
            $table->time('check_out_time')->default('11:00');
            $table->decimal('distance_to_haram', 8, 2)->nullable()->comment('Distance in KM');
            $table->decimal('distance_to_airport', 8, 2)->nullable()->comment('Distance in KM');
            
            // Features & Amenities (stored as JSON)
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            
            // Hotel Policies
            $table->text('policy_cancellation')->nullable();
            $table->text('policy_children')->nullable();
            $table->text('policy_pets')->nullable();
            
            // Status Management
            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            
            // Timestamps & Soft Delete
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['provider_id', 'status']);
            $table->index(['city', 'type']);
            $table->index(['star_rating', 'status']);
            $table->index(['is_active', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
