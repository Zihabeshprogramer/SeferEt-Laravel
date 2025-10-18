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
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('hotel_booking_id')->constrained()->onDelete('cascade');
            
            // Payment details
            $table->string('payment_reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            
            // Gateway response
            $table->json('gateway_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['hotel_booking_id', 'status']);
            $table->index(['payment_reference']);
            $table->index(['status', 'processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
