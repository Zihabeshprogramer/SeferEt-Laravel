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
        Schema::create('package_service_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('service_offer_id')->constrained('service_offers')->onDelete('cascade');
            $table->boolean('is_required')->default(false); // Whether this service is required for the package
            $table->decimal('markup_percentage', 5, 2)->default(0); // Markup percentage on service price
            $table->decimal('custom_price', 10, 2)->nullable(); // Override price for this service
            $table->json('integration_config')->nullable(); // Configuration for service integration
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['package_id', 'service_offer_id']);
            $table->unique(['package_id', 'service_offer_id']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_service_offers');
    }
};
