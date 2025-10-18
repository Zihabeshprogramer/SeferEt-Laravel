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
        Schema::table('users', function (Blueprint $table) {
            // Update role enum to include service provider types
            $table->enum('role', ['admin', 'partner', 'customer', 'hotel_provider', 'transport_provider'])
                  ->default('customer')
                  ->change();
            
            // Service provider specific fields
            $table->string('service_type')->nullable()->after('social_links');
            $table->json('service_categories')->nullable()->after('service_type');
            $table->json('coverage_areas')->nullable()->after('service_categories');
            $table->string('certification_number')->nullable()->after('coverage_areas');
            $table->json('api_credentials')->nullable()->after('certification_number');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('api_credentials');
            $table->boolean('is_api_enabled')->default(false)->after('commission_rate');
            
            // Add indexes for performance
            $table->index(['role', 'service_type']);
            $table->index(['service_type', 'is_api_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['role', 'service_type']);
            $table->dropIndex(['service_type', 'is_api_enabled']);
            
            // Drop service provider fields
            $table->dropColumn([
                'service_type',
                'service_categories',
                'coverage_areas',
                'certification_number',
                'api_credentials',
                'commission_rate',
                'is_api_enabled'
            ]);
            
            // Revert role enum to original values
            $table->enum('role', ['admin', 'partner', 'customer'])
                  ->default('customer')
                  ->change();
        });
    }
};
