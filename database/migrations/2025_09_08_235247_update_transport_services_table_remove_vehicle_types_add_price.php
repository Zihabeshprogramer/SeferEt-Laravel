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
        Schema::table('transport_services', function (Blueprint $table) {
            // Add price column after transport_type
            $table->decimal('price', 10, 2)->after('route_type')->comment('Base price per person/trip');
            
            // Make route_type nullable since routes are defined below
            $table->enum('route_type', ['airport_transfer', 'city_transport', 'intercity', 'pilgrimage_sites'])
                  ->nullable()
                  ->change();
            
            // Remove vehicle_types column since transport_type is sufficient
            $table->dropColumn('vehicle_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_services', function (Blueprint $table) {
            // Add back vehicle_types column
            $table->json('vehicle_types')->nullable()->after('routes');
            
            // Make route_type required again
            $table->enum('route_type', ['airport_transfer', 'city_transport', 'intercity', 'pilgrimage_sites'])
                  ->nullable(false)
                  ->change();
            
            // Remove price column
            $table->dropColumn('price');
        });
    }
};
