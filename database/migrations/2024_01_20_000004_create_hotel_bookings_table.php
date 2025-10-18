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
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Booking details
            $table->string('booking_reference')->unique();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights');
            $table->tinyInteger('adults')->default(1);
            $table->tinyInteger('children')->default(0);
            
            // Pricing
            $table->decimal('room_rate', 10, 2);
            $table->decimal('total_amount', 10, 2);
            
            // Status
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded', 'failed'])->default('pending');
            $table->string('payment_method')->nullable();
            
            // Guest information
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();
            $table->text('special_requests')->nullable();
            
            // Cancellation details
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            
            // Timestamps & soft delete
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['hotel_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index(['status', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_bookings');
    }
};
