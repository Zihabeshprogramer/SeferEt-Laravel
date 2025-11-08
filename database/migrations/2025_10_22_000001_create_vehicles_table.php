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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('vehicle_name');
            $table->enum('vehicle_type', ['bus', 'van', 'car', 'minibus', 'coach', 'suv', 'sedan', 'other']);
            $table->string('plate_number')->unique();
            $table->integer('capacity'); // Number of passengers
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->enum('status', ['available', 'assigned', 'under_maintenance', 'unavailable'])->default('available');
            $table->json('images')->nullable(); // Array of image URLs
            $table->json('documents')->nullable(); // Registration, insurance, etc.
            $table->json('specifications')->nullable(); // Additional specs
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['provider_id', 'is_active']);
            $table->index(['status']);
            $table->index(['vehicle_type']);
            $table->index(['provider_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
