<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Inventory Calendar for atomic availability management
     */
    public function up(): void
    {
        Schema::create('inventory_calendar', function (Blueprint $table) {
            $table->id();
            
            // Provider and service identification
            $table->enum('provider_type', ['hotel', 'flight', 'transport'])->index();
            $table->unsignedBigInteger('item_id')->index(); // hotel_id, flight_id, transport_service_id
            $table->date('date')->index(); // Specific date for availability
            
            // Capacity tracking
            $table->integer('total_capacity')->default(0); // Total rooms/seats/capacity for this date
            $table->integer('allocated_capacity')->default(0); // Currently allocated
            $table->integer('available_capacity')->default(0); // Remaining available
            $table->integer('blocked_capacity')->default(0); // Temporarily blocked (maintenance, etc.)
            
            // Pricing information
            $table->decimal('base_price', 12, 2)->nullable(); // Base price for this date
            $table->string('currency', 3)->default('USD');
            $table->json('pricing_tiers')->nullable(); // Different pricing based on quantity
            
            // Status and controls
            $table->boolean('is_available')->default(true); // Overall availability flag
            $table->boolean('is_bookable')->default(true); // Can accept new bookings
            $table->text('restriction_notes')->nullable(); // Reason for restrictions
            
            // Concurrency control - CRITICAL for atomic operations
            $table->unsignedInteger('version')->default(1); // Optimistic locking version
            $table->timestamp('last_updated_at')->nullable(); // Track updates for stale data detection
            
            // Metadata
            $table->json('metadata')->nullable(); // Service-specific data (room types, flight class, etc.)
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['provider_type', 'item_id', 'date'], 'inventory_unique');
            
            // Performance indexes
            $table->index(['provider_type', 'item_id', 'date', 'is_available'], 'availability_lookup');
            $table->index(['date', 'is_available', 'is_bookable'], 'date_availability');
            $table->index(['item_id', 'date', 'available_capacity'], 'capacity_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_calendar');
    }
};