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
        Schema::create('hotel_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('hotel_name');
            $table->text('address');
            $table->string('city');
            $table->string('country');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->tinyInteger('star_rating')->unsigned()->nullable(); // 1-5 stars
            $table->json('amenities')->nullable(); // Hotel amenities
            $table->json('room_types')->nullable(); // Available room types
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->json('policies')->nullable(); // Hotel policies
            $table->json('contact_info')->nullable(); // Contact details
            $table->json('images')->nullable(); // Hotel images
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['provider_id', 'is_active']);
            $table->index(['city', 'is_active']);
            $table->index(['star_rating', 'is_active']);
            $table->index(['latitude', 'longitude']); // For geographic queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_services');
    }
};
