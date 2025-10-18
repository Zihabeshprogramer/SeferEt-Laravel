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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('airline');
            $table->string('flight_number');
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->datetime('departure_datetime');
            $table->datetime('arrival_datetime');
            $table->integer('total_seats');
            $table->integer('available_seats');
            $table->decimal('economy_price', 10, 2);
            $table->decimal('business_price', 10, 2)->nullable();
            $table->decimal('first_class_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->string('aircraft_type')->nullable();
            $table->json('amenities')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled', 'delayed'])->default('scheduled');
            $table->json('baggage_allowance')->nullable();
            $table->json('meal_service')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['provider_id', 'is_active']);
            $table->index(['departure_airport', 'arrival_airport']);
            $table->index(['departure_datetime', 'arrival_datetime']);
            $table->index(['status', 'departure_datetime']);
            $table->index('flight_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
