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
        Schema::create('package_bookings', function (Blueprint $table) {
            $table->id();
            
            // Core booking information
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('booking_reference')->unique();
            $table->string('booking_source')->default('website'); // website, api, phone, etc.
            
            // Booking dates
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('duration_days');
            
            // Participants
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->integer('total_participants');
            
            // Customer details
            $table->string('primary_contact_name');
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone');
            $table->json('participant_details')->nullable(); // Array of participant info
            $table->text('special_requirements')->nullable();
            $table->text('dietary_requirements')->nullable();
            
            // Pricing
            $table->decimal('package_price', 10, 2);
            $table->decimal('addon_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('pending_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('pricing_breakdown')->nullable();
            
            // Payment information
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded', 'failed'])->default('pending');
            $table->enum('payment_method', ['card', 'bank_transfer', 'cash', 'paypal', 'stripe', 'other'])->nullable();
            $table->json('payment_details')->nullable();
            
            // Booking status
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->string('cancellation_reason')->nullable();
            $table->json('cancellation_policy')->nullable();
            
            // Additional services
            $table->json('selected_addons')->nullable(); // Selected optional services
            $table->json('accommodation_preferences')->nullable();
            $table->json('transport_preferences')->nullable();
            
            // Booking lifecycle timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('payment_due_date')->nullable();
            $table->timestamp('departure_reminder_sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Notes and communication
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('communication_log')->nullable();
            
            // Agent and commission info
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('agent_commission', 10, 2)->default(0);
            $table->decimal('platform_commission', 10, 2)->default(0);
            
            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Documentation and files
            $table->json('required_documents')->nullable();
            $table->json('uploaded_documents')->nullable();
            $table->boolean('documents_verified')->default(false);
            
            // Reviews and feedback
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->text('review')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            
            // Metadata and tracking
            $table->json('metadata')->nullable();
            $table->string('referral_source')->nullable();
            $table->ipAddress('booking_ip')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['status', 'departure_date']);
            $table->index(['customer_id', 'status']);
            $table->index(['agent_id', 'status']);
            $table->index(['departure_date', 'return_date']);
            $table->index('payment_status');
            $table->index('booking_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_bookings');
    }
};
