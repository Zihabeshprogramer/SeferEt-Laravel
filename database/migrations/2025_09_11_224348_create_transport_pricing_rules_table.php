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
        Schema::create('transport_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('rule_type', ['seasonal', 'advance_booking', 'route_based', 'day_of_week', 'demand_based', 'promotional', 'distance_based', 'passenger_count']);
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('transport_service_id')->nullable()->constrained('transport_services')->onDelete('cascade');
            $table->string('transport_type')->nullable(); // bus, car, van, etc.
            $table->string('route_type')->nullable(); // airport_transfer, intercity, etc.
            $table->json('specific_routes')->nullable(); // Specific route combinations
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('adjustment_type', ['percentage', 'fixed', 'multiply']);
            $table->decimal('adjustment_value', 10, 2);
            $table->integer('min_passengers')->nullable();
            $table->integer('max_passengers')->nullable();
            $table->decimal('min_distance')->nullable();
            $table->decimal('max_distance')->nullable();
            $table->json('days_of_week')->nullable(); // ['monday', 'tuesday', etc.]
            $table->integer('priority')->default(5); // 1-10 scale
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // Additional conditions
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['provider_id', 'transport_service_id']);
            $table->index(['start_date', 'end_date']);
            $table->index(['is_active', 'priority']);
            $table->index(['transport_type', 'route_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_pricing_rules');
    }
};
