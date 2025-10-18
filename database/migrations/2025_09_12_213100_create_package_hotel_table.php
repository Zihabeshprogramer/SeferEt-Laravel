<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Package-Hotel relationship table
     */
    public function up(): void
    {
        Schema::create('package_hotel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            
            // Relationship details
            $table->enum('source_type', ['platform', 'external'])->default('platform');
            $table->boolean('is_primary')->default(false); // Primary hotel for the package
            $table->boolean('is_required')->default(true);
            $table->integer('nights')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            
            // Room and pricing details
            $table->string('room_type')->nullable();
            $table->integer('rooms_needed')->default(1);
            $table->json('room_configuration')->nullable(); // Room sharing details
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            
            // Meal plans and services
            $table->json('meal_plans')->nullable(); // Breakfast, half board, etc.
            $table->json('additional_services')->nullable(); // Extra services
            $table->json('special_requests')->nullable();
            
            // Commission and revenue sharing
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->boolean('commission_shared')->default(false); // For inter-agent collaborations
            $table->json('revenue_sharing_details')->nullable();
            
            // External provider details (when source_type is external)
            $table->json('external_hotel_details')->nullable(); // Name, address, contact, etc.
            $table->enum('confirmation_status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('booking_reference')->nullable();
            $table->text('confirmation_notes')->nullable();
            
            // Availability and booking
            $table->integer('availability_checked_count')->default(0);
            $table->timestamp('last_availability_check')->nullable();
            $table->boolean('auto_confirm')->default(false);
            $table->integer('booking_deadline_days')->nullable(); // Days before check-in to finalize
            
            // Display and marketing
            $table->integer('display_order')->default(0);
            $table->text('marketing_description')->nullable();
            $table->json('features_highlighted')->nullable();
            $table->boolean('featured_in_package')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['package_id', 'hotel_id']);
            $table->index(['package_id', 'is_primary']);
            $table->index(['source_type', 'confirmation_status']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index('display_order');
            
            // Unique constraint to prevent duplicate hotel assignments
            $table->unique(['package_id', 'hotel_id', 'room_type'], 'unique_package_hotel_room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_hotel');
    }
};