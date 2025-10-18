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
        Schema::table('hotel_bookings', function (Blueprint $table) {
            // Add missing fields that the BookingIntegrationService expects
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('paid_amount');
            $table->decimal('service_fee', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('service_fee');
            $table->string('currency', 3)->default('USD')->after('discount_amount');
            $table->text('notes')->nullable()->after('special_requests');
            $table->string('confirmation_code')->nullable()->after('booking_reference');
            $table->text('cancellation_policy')->nullable()->after('cancellation_reason');
            $table->string('source')->default('manual')->after('cancellation_policy');
            $table->timestamp('checked_in_at')->nullable()->after('confirmed_at');
            $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'paid_amount',
                'tax_amount',
                'service_fee',
                'discount_amount',
                'currency',
                'notes',
                'confirmation_code',
                'cancellation_policy',
                'source',
                'checked_in_at',
                'checked_out_at'
            ]);
        });
    }
};
