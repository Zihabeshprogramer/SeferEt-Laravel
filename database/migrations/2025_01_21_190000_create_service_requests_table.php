<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Enhanced Service Request System for Package Creation
     */
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            
            // Core request information
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade'); // Travel agent who made request
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade'); // Service provider
            
            // Service details
            $table->enum('provider_type', ['hotel', 'flight', 'transport'])->index();
            $table->unsignedBigInteger('item_id')->nullable()->index(); // hotel_id, flight_id, transport_service_id
            $table->integer('requested_quantity')->nullable(); // rooms, seats, passengers
            $table->date('start_date')->nullable()->index(); // check-in, departure, service date
            $table->date('end_date')->nullable()->index(); // check-out, return, service end date
            $table->json('metadata')->nullable(); // passenger details, room preferences, special requirements
            
            // Request status and workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'cancelled'])->default('pending')->index();
            $table->text('agent_notes')->nullable(); // Agent's request notes
            $table->text('provider_notes')->nullable(); // Provider response notes
            $table->string('rejection_reason')->nullable(); // Reason for rejection
            
            // Approval details
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->integer('allocated_quantity')->nullable(); // Actual quantity allocated (may differ from requested)
            $table->json('approval_conditions')->nullable(); // Special conditions from provider
            
            // Pricing and terms
            $table->decimal('requested_price', 12, 2)->nullable();
            $table->decimal('offered_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->json('terms_and_conditions')->nullable();
            
            // Expiration and deadlines
            $table->timestamp('expires_at')->nullable()->index(); // Configurable TTL
            $table->timestamp('responded_at')->nullable();
            
            // Communication tracking
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_sent')->nullable();
            $table->json('communication_log')->nullable();
            
            // Priority and urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            $table->boolean('is_rush_request')->default(false);
            
            // Advanced features
            $table->boolean('requires_advance_payment')->default(false);
            $table->decimal('advance_payment_percentage', 5, 2)->nullable();
            $table->json('external_provider_details')->nullable(); // For external providers
            $table->boolean('is_external_provider')->default(false);
            $table->boolean('auto_approved')->default(false); // For own services
            
            // Versioning for optimistic locking
            $table->unsignedInteger('version')->default(1);
            
            $table->timestamps();
            
            // Composite indexes for performance
            $table->index(['package_id', 'provider_type']);
            $table->index(['provider_id', 'status', 'expires_at']);
            $table->index(['agent_id', 'status']);
            $table->index(['status', 'priority', 'created_at']);
            $table->index(['provider_type', 'item_id', 'start_date']);
            $table->index(['expires_at', 'status']); // For cleanup jobs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};