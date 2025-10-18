<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Provider Request System for Package Creation
     */
    public function up(): void
    {
        Schema::create('provider_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('travel_agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            
            // Request details
            $table->enum('service_type', ['hotel', 'flight', 'transport']);
            $table->string('service_id')->nullable(); // ID of specific service (hotel_id, flight_id, etc.)
            $table->json('request_details'); // Detailed request information
            
            // Request status
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->text('provider_notes')->nullable(); // Provider response notes
            $table->text('travel_agent_notes')->nullable(); // Travel agent request notes
            
            // Pricing and terms
            $table->decimal('requested_price', 12, 2)->nullable();
            $table->decimal('offered_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->json('terms_and_conditions')->nullable();
            
            // Dates and deadlines
            $table->datetime('service_start_date')->nullable();
            $table->datetime('service_end_date')->nullable();
            $table->datetime('response_deadline')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->datetime('rejected_at')->nullable();
            
            // Approval details
            $table->json('approval_conditions')->nullable(); // Any special conditions
            $table->boolean('requires_advance_payment')->default(false);
            $table->decimal('advance_payment_percentage', 5, 2)->nullable();
            
            // External provider details (when using external providers)
            $table->json('external_provider_details')->nullable();
            $table->boolean('is_external_provider')->default(false);
            
            // Communication tracking
            $table->integer('reminder_count')->default(0);
            $table->datetime('last_reminder_sent')->nullable();
            $table->json('communication_log')->nullable();
            
            // Priority and urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_rush_request')->default(false);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['package_id', 'service_type']);
            $table->index(['provider_id', 'status']);
            $table->index(['travel_agent_id', 'status']);
            $table->index(['status', 'response_deadline']);
            $table->index(['service_type', 'status']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_requests');
    }
};
