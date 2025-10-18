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
        Schema::create('flight_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('booking_reference')->unique();
            $table->string('confirmation_code')->nullable();
            $table->integer('passengers');
            $table->string('flight_class')->default('economy');
            $table->decimal('seat_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('service_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('passenger_name');
            $table->string('passenger_email');
            $table->string('passenger_phone')->nullable();
            $table->json('passenger_details')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('source')->default('manual');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('check_in_opened_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['flight_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index(['booking_reference']);
            $table->index(['source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_bookings');
    }
};
