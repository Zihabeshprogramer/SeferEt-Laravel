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
        Schema::create('transport_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('service_name');
            $table->enum('transport_type', ['bus', 'car', 'van', 'taxi', 'shuttle', 'flight']);
            $table->enum('route_type', ['airport_transfer', 'city_transport', 'intercity', 'pilgrimage_sites']);
            $table->json('routes')->nullable(); // Available routes
            $table->json('vehicle_types')->nullable(); // Available vehicle types
            $table->json('specifications')->nullable(); // Vehicle specifications
            $table->integer('max_passengers')->default(1);
            $table->json('pickup_locations')->nullable(); // Pickup points
            $table->json('dropoff_locations')->nullable(); // Drop-off points
            $table->json('operating_hours')->nullable(); // Operating schedule
            $table->json('policies')->nullable(); // Service policies
            $table->json('contact_info')->nullable(); // Contact details
            $table->json('images')->nullable(); // Service/vehicle images
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['provider_id', 'is_active']);
            $table->index(['transport_type', 'is_active']);
            $table->index(['route_type', 'is_active']);
            $table->index(['transport_type', 'route_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_services');
    }
};
