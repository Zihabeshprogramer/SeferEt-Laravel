<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add own_service flag to package relations for auto-approval
     */
    public function up(): void
    {
        // Add own_service flag to package_hotel pivot table
        if (Schema::hasTable('package_hotel')) {
            Schema::table('package_hotel', function (Blueprint $table) {
                if (!Schema::hasColumn('package_hotel', 'own_service')) {
                    $table->boolean('own_service')->default(false)->after('featured_in_package');
                    $table->index(['own_service'], 'package_hotel_own_service_idx');
                }
            });
        }

        // Add own_service flag to package_flight pivot table  
        if (Schema::hasTable('package_flight')) {
            Schema::table('package_flight', function (Blueprint $table) {
                if (!Schema::hasColumn('package_flight', 'own_service')) {
                    $table->boolean('own_service')->default(false)->after('seats_allocated');
                    $table->index(['own_service'], 'package_flight_own_service_idx');
                }
            });
        }

        // Add own_service flag to package_transport pivot table
        if (Schema::hasTable('package_transport')) {
            Schema::table('package_transport', function (Blueprint $table) {
                if (!Schema::hasColumn('package_transport', 'own_service')) {
                    $table->boolean('own_service')->default(false)->after('display_order');
                    $table->index(['own_service'], 'package_transport_own_service_idx');
                }
            });
        }

        // Add own_service flag to package_service_offers pivot table
        if (Schema::hasTable('package_service_offers')) {
            Schema::table('package_service_offers', function (Blueprint $table) {
                if (!Schema::hasColumn('package_service_offers', 'own_service')) {
                    $table->boolean('own_service')->default(false)->after('integration_config');
                    $table->index(['own_service'], 'package_service_offers_own_service_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove own_service flag from package_hotel pivot table
        if (Schema::hasTable('package_hotel') && Schema::hasColumn('package_hotel', 'own_service')) {
            Schema::table('package_hotel', function (Blueprint $table) {
                $table->dropIndex('package_hotel_own_service_idx');
                $table->dropColumn('own_service');
            });
        }

        // Remove own_service flag from package_flight pivot table  
        if (Schema::hasTable('package_flight') && Schema::hasColumn('package_flight', 'own_service')) {
            Schema::table('package_flight', function (Blueprint $table) {
                $table->dropIndex('package_flight_own_service_idx');
                $table->dropColumn('own_service');
            });
        }

        // Remove own_service flag from package_transport pivot table
        if (Schema::hasTable('package_transport') && Schema::hasColumn('package_transport', 'own_service')) {
            Schema::table('package_transport', function (Blueprint $table) {
                $table->dropIndex('package_transport_own_service_idx');
                $table->dropColumn('own_service');
            });
        }

        // Remove own_service flag from package_service_offers pivot table
        if (Schema::hasTable('package_service_offers') && Schema::hasColumn('package_service_offers', 'own_service')) {
            Schema::table('package_service_offers', function (Blueprint $table) {
                $table->dropIndex('package_service_offers_own_service_idx');
                $table->dropColumn('own_service');
            });
        }
    }
};