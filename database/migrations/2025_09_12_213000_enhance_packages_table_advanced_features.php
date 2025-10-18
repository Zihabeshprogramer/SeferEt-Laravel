<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Enhanced Package Creation Module
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Enhanced date and availability fields
            $table->date('start_date')->nullable()->after('duration');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('min_participants')->default(1)->after('max_participants');
            $table->integer('current_bookings')->default(0)->after('min_participants');
            $table->json('booking_deadlines')->nullable()->after('current_bookings');
            
            // Enhanced features and activities
            $table->json('features')->nullable()->after('inclusions'); // Detailed features checklist
            $table->json('activities')->nullable()->after('features'); // Day-by-day activities
            $table->longText('detailed_description')->nullable()->after('description'); // Rich text description
            $table->json('highlights')->nullable()->after('detailed_description'); // Key highlights
            $table->json('special_offers')->nullable()->after('highlights'); // Special promotions
            
            // Enhanced pricing structure
            $table->json('pricing_breakdown')->nullable()->after('base_price'); // Detailed pricing
            $table->json('optional_addons')->nullable()->after('pricing_breakdown'); // Optional add-ons
            $table->decimal('total_price', 12, 2)->nullable()->after('optional_addons'); // Calculated total
            $table->json('payment_terms')->nullable()->after('total_price'); // Payment terms
            $table->decimal('deposit_percentage', 5, 2)->default(30.00)->after('payment_terms');
            $table->json('cancellation_policy')->nullable()->after('deposit_percentage');
            
            // Provider management
            $table->enum('hotel_source', ['platform', 'external', 'mixed'])->default('platform')->after('uses_b2b_services');
            $table->enum('transport_source', ['platform', 'external', 'mixed'])->default('platform')->after('hotel_source');
            $table->enum('flight_source', ['own', 'platform', 'external', 'mixed'])->default('own')->after('transport_source');
            $table->json('external_providers')->nullable()->after('flight_source'); // External provider details
            
            // Commission and revenue sharing
            $table->json('commission_structure')->nullable()->after('external_providers');
            $table->decimal('platform_commission', 5, 2)->default(10.00)->after('commission_structure');
            $table->json('revenue_sharing')->nullable()->after('platform_commission');
            
            // Enhanced status and workflow
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'needs_revision'])->default('pending')->after('status');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approved_at');
            $table->integer('version')->default(1)->after('approved_by');
            $table->json('draft_data')->nullable()->after('version'); // Draft save functionality
            
            // Location and logistics
            $table->json('meeting_points')->nullable()->after('departure_cities');
            $table->json('pickup_locations')->nullable()->after('meeting_points');
            $table->json('accommodation_preferences')->nullable()->after('pickup_locations');
            $table->json('transport_preferences')->nullable()->after('accommodation_preferences');
            
            // Documentation and requirements
            $table->json('required_documents')->nullable()->after('transport_preferences');
            $table->json('visa_requirements')->nullable()->after('required_documents');
            $table->json('health_requirements')->nullable()->after('visa_requirements');
            $table->json('age_restrictions')->nullable()->after('health_requirements');
            
            // Marketing and SEO
            $table->string('slug')->unique()->nullable()->after('name');
            $table->json('seo_meta')->nullable()->after('slug'); // SEO metadata
            $table->json('tags')->nullable()->after('seo_meta'); // Searchable tags
            $table->integer('views_count')->default(0)->after('tags');
            $table->integer('bookings_count')->default(0)->after('views_count');
            $table->decimal('average_rating', 3, 2)->default(0)->after('bookings_count');
            $table->integer('reviews_count')->default(0)->after('average_rating');
            
            // Advanced features
            $table->json('customization_options')->nullable()->after('reviews_count');
            $table->json('group_discounts')->nullable()->after('customization_options');
            $table->json('seasonal_pricing')->nullable()->after('group_discounts');
            $table->json('multi_language')->nullable()->after('seasonal_pricing'); // Multi-language support
            $table->boolean('is_premium')->default(false)->after('is_featured');
            $table->boolean('allow_customization')->default(false)->after('is_premium');
            $table->boolean('instant_booking')->default(false)->after('allow_customization');
            
            // Analytics and tracking
            $table->json('analytics_data')->nullable()->after('instant_booking');
            $table->timestamp('last_updated_pricing')->nullable()->after('analytics_data');
            $table->timestamp('last_availability_check')->nullable()->after('last_updated_pricing');
            
            // Add comprehensive indexes for performance
            $table->index(['start_date', 'end_date']);
            $table->index(['hotel_source', 'transport_source', 'flight_source']);
            $table->index(['approval_status', 'status']);
            $table->index(['is_premium', 'is_featured']);
            $table->index(['average_rating', 'reviews_count']);
            $table->index('views_count');
            $table->index('bookings_count');
            $table->index('slug');
            $table->index(['min_participants', 'max_participants']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropIndex(['hotel_source', 'transport_source', 'flight_source']);
            $table->dropIndex(['approval_status', 'status']);
            $table->dropIndex(['is_premium', 'is_featured']);
            $table->dropIndex(['average_rating', 'reviews_count']);
            $table->dropIndex(['views_count']);
            $table->dropIndex(['bookings_count']);
            $table->dropIndex(['slug']);
            $table->dropIndex(['min_participants', 'max_participants']);
            
            // Drop foreign key constraint
            $table->dropForeign(['approved_by']);
            
            // Drop columns
            $table->dropColumn([
                'start_date', 'end_date', 'min_participants', 'current_bookings', 'booking_deadlines',
                'features', 'activities', 'detailed_description', 'highlights', 'special_offers',
                'pricing_breakdown', 'optional_addons', 'total_price', 'payment_terms', 
                'deposit_percentage', 'cancellation_policy',
                'hotel_source', 'transport_source', 'flight_source', 'external_providers',
                'commission_structure', 'platform_commission', 'revenue_sharing',
                'approval_status', 'rejection_reason', 'approved_at', 'approved_by', 'version', 'draft_data',
                'meeting_points', 'pickup_locations', 'accommodation_preferences', 'transport_preferences',
                'required_documents', 'visa_requirements', 'health_requirements', 'age_restrictions',
                'slug', 'seo_meta', 'tags', 'views_count', 'bookings_count', 'average_rating', 'reviews_count',
                'customization_options', 'group_discounts', 'seasonal_pricing', 'multi_language',
                'is_premium', 'allow_customization', 'instant_booking',
                'analytics_data', 'last_updated_pricing', 'last_availability_check'
            ]);
        });
    }
};