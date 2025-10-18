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
        Schema::create('service_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->json('specifications')->nullable(); // Service-specific details
            $table->decimal('base_price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->json('pricing_rules')->nullable(); // Dynamic pricing rules
            $table->integer('max_capacity')->nullable();
            $table->json('availability')->nullable(); // Availability calendar/rules
            $table->enum('status', ['active', 'inactive', 'draft', 'suspended'])->default('draft');
            $table->json('terms_conditions')->nullable();
            $table->json('cancellation_policy')->nullable();
            $table->boolean('is_api_integrated')->default(false);
            $table->json('api_mapping')->nullable(); // For API integration mapping
            
            // Polymorphic relationship to specific service types
            $table->morphs('service'); // Creates service_id and service_type columns
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['provider_id', 'status']);
            $table->index(['service_type', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_offers');
    }
};
