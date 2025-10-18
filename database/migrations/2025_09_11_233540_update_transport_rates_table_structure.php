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
        Schema::table('transport_rates', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique('transport_rates_unique');
            
            // Add new columns
            $table->foreignId('provider_id')->after('transport_service_id')->constrained('users')->onDelete('cascade');
            $table->enum('passenger_type', ['adult', 'child', 'infant'])->after('route_to');
            $table->string('currency', 3)->after('price')->default('USD');
            $table->boolean('is_available')->after('notes')->default(true);
            
            // Rename price to base_rate
            $table->renameColumn('price', 'base_rate');
            
            // Rename is_active to a different name temporarily to avoid conflicts
            $table->renameColumn('is_active', 'temp_is_active');
        });
        
        // Second schema call to handle the rename back and drop columns
        Schema::table('transport_rates', function (Blueprint $table) {
            // Drop columns we don't need
            $table->dropColumn([
                'min_passengers',
                'max_passengers', 
                'distance_km',
                'duration_minutes',
                'temp_is_active',
                'additional_data'
            ]);
            
            // Add new unique constraint
            $table->unique([
                'transport_service_id', 
                'provider_id', 
                'date', 
                'route_from', 
                'route_to', 
                'passenger_type'
            ], 'transport_rates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_rates', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('transport_rates_unique');
            
            // Add back dropped columns
            $table->integer('min_passengers')->default(1);
            $table->integer('max_passengers');
            $table->decimal('distance_km')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('additional_data')->nullable();
            
            // Drop new columns
            $table->dropForeign(['provider_id']);
            $table->dropColumn(['provider_id', 'passenger_type', 'currency', 'is_available']);
            
            // Rename base_rate back to price
            $table->renameColumn('base_rate', 'price');
            
            // Add back original unique constraint
            $table->unique(['transport_service_id', 'date', 'route_from', 'route_to'], 'transport_rates_unique');
        });
    }
};
