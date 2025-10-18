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
        Schema::table('flights', function (Blueprint $table) {
            // Round-trip flight support
            $table->enum('trip_type', ['one_way', 'round_trip'])->default('round_trip')->after('flight_number');
            $table->string('return_flight_number')->nullable()->after('trip_type');
            $table->datetime('return_departure_datetime')->nullable()->after('arrival_datetime');
            $table->datetime('return_arrival_datetime')->nullable()->after('return_departure_datetime');
            
            // Group booking support
            $table->boolean('is_group_booking')->default(true)->after('is_active');
            $table->integer('min_group_size')->default(10)->after('is_group_booking');
            $table->integer('max_group_size')->default(50)->after('min_group_size');
            $table->decimal('group_discount_percentage', 5, 2)->default(0)->after('max_group_size');
            $table->date('booking_deadline')->nullable()->after('group_discount_percentage');
            
            // Agent collaboration support
            $table->boolean('allows_agent_collaboration')->default(true)->after('booking_deadline');
            $table->decimal('collaboration_commission_percentage', 5, 2)->default(5.00)->after('allows_agent_collaboration');
            $table->text('collaboration_terms')->nullable()->after('collaboration_commission_percentage');
            
            // Pricing structure for groups
            $table->decimal('group_economy_price', 10, 2)->nullable()->after('economy_price');
            $table->decimal('group_business_price', 10, 2)->nullable()->after('business_price');
            $table->decimal('group_first_class_price', 10, 2)->nullable()->after('first_class_price');
            
            // Additional group booking information
            $table->json('included_services')->nullable()->after('meal_service');
            $table->text('special_requirements')->nullable()->after('included_services');
            $table->enum('payment_terms', ['full_upfront', '50_percent_deposit', '30_percent_deposit'])->default('50_percent_deposit')->after('special_requirements');
            
            // Add indexes for new fields
            $table->index(['is_group_booking', 'booking_deadline']);
            $table->index(['allows_agent_collaboration']);
            $table->index(['trip_type', 'departure_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn([
                'trip_type',
                'return_flight_number',
                'return_departure_datetime', 
                'return_arrival_datetime',
                'is_group_booking',
                'min_group_size',
                'max_group_size',
                'group_discount_percentage',
                'booking_deadline',
                'allows_agent_collaboration',
                'collaboration_commission_percentage',
                'collaboration_terms',
                'group_economy_price',
                'group_business_price',
                'group_first_class_price',
                'included_services',
                'special_requirements',
                'payment_terms'
            ]);
        });
    }
};
