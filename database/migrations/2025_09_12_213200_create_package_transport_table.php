<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Package-Transport relationship table
     */
    public function up(): void
    {
        Schema::create('package_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('transport_service_id')->constrained()->onDelete('cascade');
            
            // Relationship details
            $table->enum('source_type', ['platform', 'external'])->default('platform');
            $table->enum('transport_category', ['airport_transfer', 'city_transport', 'intercity', 'pilgrimage_sites', 'custom'])->default('airport_transfer');
            $table->boolean('is_required')->default(true);
            $table->integer('day_of_itinerary')->nullable(); // Which day of the package
            
            // Route and scheduling details
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->json('route_details')->nullable(); // Intermediate stops
            $table->datetime('scheduled_pickup_time')->nullable();
            $table->datetime('scheduled_dropoff_time')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->integer('distance_km')->nullable();
            
            // Capacity and passenger details
            $table->integer('passengers_count');
            $table->integer('luggage_pieces')->nullable();
            $table->json('special_requirements')->nullable(); // Wheelchair access, etc.
            $table->json('passenger_details')->nullable(); // Names, ages for specific bookings
            
            // Pricing details
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('pricing_type', ['per_person', 'per_vehicle', 'per_trip'])->default('per_person');
            
            // Commission and revenue sharing
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->boolean('commission_shared')->default(false);
            $table->json('revenue_sharing_details')->nullable();
            
            // External provider details (when source_type is external)
            $table->json('external_transport_details')->nullable(); // Company, vehicle, driver details
            $table->enum('confirmation_status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('booking_reference')->nullable();
            $table->text('confirmation_notes')->nullable();
            $table->json('driver_details')->nullable(); // Name, phone, license
            $table->json('vehicle_details')->nullable(); // Make, model, plate number
            
            // Availability and booking
            $table->timestamp('last_availability_check')->nullable();
            $table->boolean('auto_confirm')->default(false);
            $table->integer('booking_deadline_hours')->nullable(); // Hours before pickup to finalize
            $table->boolean('allows_modification')->default(true);
            $table->json('modification_policy')->nullable();
            
            // Service preferences
            $table->boolean('air_conditioning')->default(true);
            $table->boolean('wifi_available')->default(false);
            $table->boolean('refreshments_included')->default(false);
            $table->json('amenities')->nullable();
            $table->enum('vehicle_type_preference', ['economy', 'standard', 'premium', 'luxury'])->nullable();
            
            // Display and marketing
            $table->integer('display_order')->default(0);
            $table->text('marketing_description')->nullable();
            $table->json('features_highlighted')->nullable();
            $table->boolean('featured_in_package')->default(false);
            
            // Operational details
            $table->json('contact_instructions')->nullable(); // How to contact driver/company
            $table->text('pickup_instructions')->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->boolean('tracking_available')->default(false);
            $table->string('tracking_method')->nullable(); // GPS, phone, etc.
            
            $table->timestamps();
            
            // Indexes
            $table->index(['package_id', 'transport_service_id'], 'pkg_transport_ids');
            $table->index(['package_id', 'transport_category'], 'pkg_transport_category');
            $table->index(['source_type', 'confirmation_status'], 'transport_source_status');
            $table->index(['scheduled_pickup_time', 'scheduled_dropoff_time'], 'transport_schedule_times');
            $table->index('display_order', 'transport_display_order');
            $table->index('day_of_itinerary', 'transport_itinerary_day');
            
            // Composite index for route searching
            $table->index(['pickup_location', 'dropoff_location'], 'transport_route_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_transport');
    }
};