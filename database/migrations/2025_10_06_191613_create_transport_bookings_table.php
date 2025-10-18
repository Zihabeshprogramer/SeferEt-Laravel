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
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_service_id')->constrained('transport_services')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('booking_reference')->unique();
            
            // Trip details
            $table->string('transport_type'); // bus, car, van, taxi, shuttle
            $table->string('route_type'); // airport_transfer, city_transport, intercity, pilgrimage_sites
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->dateTime('pickup_datetime');
            $table->dateTime('dropoff_datetime')->nullable();
            $table->integer('duration_minutes')->nullable();
            
            // Passenger details
            $table->integer('passenger_count');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            
            // Pricing
            $table->decimal('base_rate', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Status management
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded', 'failed'])->default('pending');
            $table->string('payment_method')->nullable();
            
            // Customer information
            $table->string('passenger_name');
            $table->string('passenger_email');
            $table->string('passenger_phone');
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            
            // Booking management
            $table->string('confirmation_code')->unique();
            $table->string('cancellation_reason')->nullable();
            $table->json('cancellation_policy')->nullable();
            $table->string('source')->default('direct'); // direct, service_request, api, etc.
            
            // Timestamps for status changes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Additional metadata
            $table->json('vehicle_details')->nullable(); // vehicle info, license plate, etc.
            $table->json('driver_details')->nullable(); // driver name, contact, etc.
            $table->json('route_details')->nullable(); // waypoints, estimated times, etc.
            $table->json('metadata')->nullable(); // flexible additional data
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'pickup_datetime']);
            $table->index(['customer_id', 'status']);
            $table->index('booking_reference');
            $table->index('confirmation_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_bookings');
    }
};
