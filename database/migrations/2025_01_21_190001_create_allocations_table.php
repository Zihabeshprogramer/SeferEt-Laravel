<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Allocations table for inventory reservation management
     */
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            
            // Reference to service request
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
            
            // Provider and service information
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->enum('provider_type', ['hotel', 'flight', 'transport'])->index();
            $table->unsignedBigInteger('item_id')->nullable()->index(); // hotel_id, flight_id, transport_service_id
            
            // Allocation details
            $table->integer('quantity')->default(0); // Number of rooms, seats, passengers allocated
            $table->date('start_date')->nullable()->index(); // Allocation start date
            $table->date('end_date')->nullable()->index(); // Allocation end date
            
            // Status and lifecycle
            $table->enum('status', ['active', 'released', 'expired', 'used', 'cancelled'])->default('active')->index();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index(); // When allocation expires
            $table->timestamp('released_at')->nullable(); // When allocation was released
            $table->timestamp('used_at')->nullable(); // When allocation was consumed
            
            // Allocation metadata
            $table->json('metadata')->nullable(); // Specific allocation details (room numbers, seat numbers, etc.)
            $table->text('allocation_reference')->nullable(); // Provider's internal reference
            $table->text('notes')->nullable(); // Additional notes
            
            // Financial tracking
            $table->decimal('allocated_price', 12, 2)->nullable(); // Price locked at allocation time
            $table->string('currency', 3)->default('USD');
            $table->decimal('commission_amount', 12, 2)->nullable();
            
            // Concurrency control
            $table->unsignedInteger('version')->default(1); // For optimistic locking
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_reason')->nullable();
            
            $table->timestamps();
            
            // Composite indexes for performance
            $table->index(['service_request_id', 'status']);
            $table->index(['provider_id', 'provider_type', 'status']);
            $table->index(['provider_type', 'item_id', 'start_date', 'end_date']);
            $table->index(['status', 'expires_at']); // For cleanup jobs
            $table->index(['start_date', 'end_date', 'status']); // For availability queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};