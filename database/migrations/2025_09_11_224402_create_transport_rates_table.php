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
        Schema::create('transport_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_service_id')->constrained('transport_services')->onDelete('cascade');
            $table->date('date');
            $table->string('route_from'); // Starting location
            $table->string('route_to'); // Destination location
            $table->decimal('price', 10, 2);
            $table->integer('min_passengers')->default(1);
            $table->integer('max_passengers');
            $table->decimal('distance_km')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('additional_data')->nullable(); // For extra route-specific data
            $table->timestamps();
            
            // Unique constraint to prevent duplicate rates for same service/date/route
            $table->unique(['transport_service_id', 'date', 'route_from', 'route_to'], 'transport_rates_unique');
            
            // Indexes for performance
            $table->index(['transport_service_id', 'date']);
            $table->index(['route_from', 'route_to']);
            $table->index(['date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_rates');
    }
};
