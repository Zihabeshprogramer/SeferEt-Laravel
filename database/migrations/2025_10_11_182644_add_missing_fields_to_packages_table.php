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
        Schema::table('packages', function (Blueprint $table) {
            // Destination and categorization fields
            $table->json('destinations')->nullable()->after('tags');
            $table->json('categories')->nullable()->after('destinations');
            $table->string('difficulty_level')->nullable()->after('categories');
            
            // Inclusion fields
            $table->boolean('includes_meals')->default(false)->after('uses_b2b_services');
            $table->boolean('includes_accommodation')->default(false)->after('includes_meals');
            $table->boolean('includes_transport')->default(false)->after('includes_accommodation');
            $table->boolean('includes_guide')->default(false)->after('includes_transport');
            $table->boolean('includes_flights')->default(false)->after('includes_guide');
            $table->boolean('includes_activities')->default(false)->after('includes_flights');
            $table->boolean('free_cancellation')->default(false)->after('includes_activities');
            $table->boolean('instant_confirmation')->default(false)->after('free_cancellation');
            
            // Pricing fields
            $table->decimal('child_price', 10, 2)->nullable()->after('base_price');
            $table->decimal('child_discount_percent', 5, 2)->nullable()->after('child_price');
            $table->decimal('infant_price', 10, 2)->nullable()->after('child_discount_percent');
            $table->decimal('single_supplement', 10, 2)->nullable()->after('infant_price');
            
            // Booking requirements
            $table->integer('min_booking_days')->nullable()->after('booking_deadlines');
            $table->boolean('requires_deposit')->default(false)->after('min_booking_days');
            
            // Additional fields from form
            $table->string('short_description', 300)->nullable()->after('description');
            $table->boolean('child_price_disabled')->default(false)->after('child_price');
            $table->boolean('child_discount_percent_disabled')->default(false)->after('child_discount_percent');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('platform_commission');
            $table->json('terms_accepted')->nullable()->after('cancellation_policy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'destinations',
                'categories',
                'difficulty_level',
                'includes_meals',
                'includes_accommodation', 
                'includes_transport',
                'includes_guide',
                'includes_flights',
                'includes_activities',
                'free_cancellation',
                'instant_confirmation',
                'child_price',
                'child_discount_percent',
                'infant_price',
                'single_supplement',
                'min_booking_days',
                'requires_deposit',
                'short_description',
                'child_price_disabled',
                'child_discount_percent_disabled',
                'commission_rate',
                'terms_accepted',
            ]);
        });
    }
};
