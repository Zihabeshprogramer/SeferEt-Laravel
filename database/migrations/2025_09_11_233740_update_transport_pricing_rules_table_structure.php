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
        Schema::table('transport_pricing_rules', function (Blueprint $table) {
            // Rename name to rule_name
            $table->renameColumn('name', 'rule_name');
        });
        
        Schema::table('transport_pricing_rules', function (Blueprint $table) {
            // Add description column
            $table->text('description')->nullable()->after('rule_name');
            
            // Update rule_type enum to match our model
            $table->dropColumn('rule_type');
        });
        
        Schema::table('transport_pricing_rules', function (Blueprint $table) {
            $table->enum('rule_type', [
                'seasonal', 'distance', 'passenger_count', 'route_specific', 
                'day_of_week', 'advance_booking'
            ])->after('description');
            
            // Update adjustment_type enum
            $table->dropColumn('adjustment_type');
        });
        
        Schema::table('transport_pricing_rules', function (Blueprint $table) {
            $table->enum('adjustment_type', ['percentage', 'fixed', 'multiplier'])->after('end_date');
            
            // Add new columns
            $table->json('applicable_routes')->nullable()->after('specific_routes');
            
            // Remove columns we don't need
            $table->dropColumn([
                'transport_type',
                'route_type',
                'specific_routes',
                'min_distance',
                'max_distance'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_pricing_rules', function (Blueprint $table) {
            // Add back dropped columns
            $table->string('transport_type')->nullable();
            $table->string('route_type')->nullable();
            $table->json('specific_routes')->nullable();
            $table->decimal('min_distance')->nullable();
            $table->decimal('max_distance')->nullable();
            
            // Drop new columns
            $table->dropColumn(['description', 'applicable_routes']);
            
            // Rename rule_name back to name
            $table->renameColumn('rule_name', 'name');
            
            // Update enums back to original
            $table->dropColumn(['rule_type', 'adjustment_type']);
        });
        
        Schema::table('transport_pricing_rules', function (Blueprint $table) {
            $table->enum('rule_type', ['seasonal', 'advance_booking', 'route_based', 'day_of_week', 'demand_based', 'promotional', 'distance_based', 'passenger_count']);
            $table->enum('adjustment_type', ['percentage', 'fixed', 'multiply']);
        });
    }
};
