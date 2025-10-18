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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['economy', 'standard', 'premium', 'luxury'])->default('standard');
            $table->integer('duration'); // Duration in days
            $table->decimal('base_price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->json('inclusions')->nullable(); // What's included
            $table->json('exclusions')->nullable(); // What's not included
            $table->json('itinerary')->nullable(); // Day-by-day itinerary
            $table->enum('status', ['draft', 'active', 'inactive', 'suspended'])->default('draft');
            $table->boolean('uses_b2b_services')->default(false);
            $table->json('service_preferences')->nullable(); // Preferred service types, etc.
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['creator_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('uses_b2b_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
