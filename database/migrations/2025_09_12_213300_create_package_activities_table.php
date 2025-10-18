<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Package Activities for detailed itinerary management
     */
    public function up(): void
    {
        Schema::create('package_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            
            // Activity details
            $table->integer('day_number'); // Day 1, Day 2, etc.
            $table->string('activity_name');
            $table->text('description');
            $table->longText('detailed_description')->nullable(); // Rich text description
            $table->json('highlights')->nullable(); // Key highlights of the activity
            
            // Timing and scheduling
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->enum('time_type', ['fixed', 'flexible', 'approximate'])->default('approximate');
            $table->json('alternative_times')->nullable(); // Alternative time slots
            
            // Location details
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('location_details')->nullable(); // Meeting points, landmarks
            $table->text('directions')->nullable();
            
            // Activity classification
            $table->enum('category', [
                'religious', 'cultural', 'educational', 'recreational', 
                'shopping', 'dining', 'transport', 'accommodation',
                'free_time', 'optional', 'group', 'individual'
            ])->default('cultural');
            $table->enum('difficulty_level', ['easy', 'moderate', 'challenging', 'expert'])->default('easy');
            $table->json('age_restrictions')->nullable(); // Min/max age, family-friendly
            $table->json('physical_requirements')->nullable(); // Fitness level needed
            
            // Pricing and options
            $table->boolean('is_included')->default(true);
            $table->decimal('additional_cost', 10, 2)->nullable(); // Extra cost if not included
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_optional')->default(false);
            $table->boolean('requires_booking')->default(false);
            $table->json('booking_details')->nullable(); // How to book, deadlines
            
            // Group and capacity management
            $table->integer('min_participants')->nullable();
            $table->integer('max_participants')->nullable();
            $table->integer('optimal_group_size')->nullable();
            $table->boolean('allows_individual_booking')->default(true);
            $table->json('group_requirements')->nullable();
            
            // Requirements and preparations
            $table->json('required_items')->nullable(); // What to bring
            $table->json('recommended_items')->nullable(); // Suggested items
            $table->json('dress_code')->nullable(); // Clothing requirements
            $table->json('weather_considerations')->nullable();
            $table->text('preparation_notes')->nullable();
            
            // Service providers and guides
            $table->json('guide_details')->nullable(); // Guide info, languages
            $table->json('provider_details')->nullable(); // Third-party providers
            $table->boolean('guide_included')->default(false);
            $table->decimal('guide_cost', 10, 2)->nullable();
            $table->json('contact_details')->nullable(); // Emergency contacts
            
            // Media and documentation
            $table->json('images')->nullable(); // Activity images
            $table->json('videos')->nullable(); // Activity videos
            $table->json('documents')->nullable(); // Maps, brochures, tickets
            $table->string('external_link')->nullable(); // Website, booking link
            
            // Special features and amenities
            $table->json('amenities')->nullable(); // Available facilities
            $table->json('accessibility_features')->nullable(); // Wheelchair access, etc.
            $table->json('dietary_accommodations')->nullable(); // Halal, vegetarian, etc.
            $table->boolean('photo_opportunities')->default(false);
            $table->boolean('shopping_available')->default(false);
            
            // Weather and seasonal information
            $table->json('seasonal_availability')->nullable(); // When activity is available
            $table->json('weather_dependency')->nullable(); // Weather requirements
            $table->json('alternative_activities')->nullable(); // Backup activities
            
            // Marketing and display
            $table->integer('display_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_highlight')->default(false); // Package highlight activity
            $table->json('marketing_tags')->nullable(); // Instagram-worthy, family-fun, etc.
            $table->text('marketing_description')->nullable();
            
            // Booking and confirmation
            $table->enum('booking_status', ['not_required', 'pending', 'confirmed', 'cancelled'])->default('not_required');
            $table->text('booking_reference')->nullable();
            $table->json('confirmation_details')->nullable();
            $table->timestamp('booking_deadline')->nullable();
            
            // Reviews and feedback
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->json('feedback_summary')->nullable();
            
            // Operational details
            $table->boolean('requires_transport')->default(false);
            $table->json('transport_details')->nullable(); // How to get there
            $table->boolean('meals_included')->default(false);
            $table->json('meal_details')->nullable(); // Type of meals
            $table->json('safety_information')->nullable();
            $table->json('emergency_procedures')->nullable();
            
            // Status and availability
            $table->boolean('is_active')->default(true);
            $table->enum('availability_status', ['available', 'limited', 'sold_out', 'suspended'])->default('available');
            $table->json('availability_calendar')->nullable(); // Date-specific availability
            $table->text('status_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['package_id', 'day_number']);
            $table->index(['package_id', 'display_order']);
            $table->index(['category', 'is_active']);
            $table->index(['is_optional', 'is_included']);
            $table->index(['start_time', 'end_time']);
            $table->index(['is_featured', 'is_highlight']);
            $table->index('booking_status');
            $table->index('availability_status');
            
            // Unique constraint to prevent duplicate activities on same day
            $table->unique(['package_id', 'day_number', 'activity_name', 'start_time'], 'unique_package_day_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_activities');
    }
};