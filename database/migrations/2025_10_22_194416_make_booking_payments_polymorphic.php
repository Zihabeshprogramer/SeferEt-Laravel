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
        // Check if column already exists (migration might have partially run)
        $hasBookableId = Schema::hasColumn('booking_payments', 'bookable_id');
        $hasHotelBookingId = Schema::hasColumn('booking_payments', 'bookable_id');
        
        if (!$hasBookableId && $hasHotelBookingId) {
            Schema::table('booking_payments', function (Blueprint $table) {
                // Drop the old foreign key constraint if it exists
                try {
                    $table->dropForeign(['hotel_booking_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                
                // Rename hotel_booking_id to bookable_id
                $table->renameColumn('hotel_booking_id', 'bookable_id');
            });
        }
        
        // Add bookable_type if it doesn't exist
        if (!Schema::hasColumn('booking_payments', 'bookable_type')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                // Add the bookable_type column for polymorphic relationship
                $table->string('bookable_type')->after('id')->nullable();
            });
            
            // Update existing records to be HotelBooking type
            \DB::statement("UPDATE booking_payments SET bookable_type = 'App\\\\Models\\\\HotelBooking' WHERE bookable_id IS NOT NULL");
        }
        
        // Add indexes if they don't exist
        Schema::table('booking_payments', function (Blueprint $table) {
            // Add indexes for polymorphic relationship
            if (!Schema::hasIndex('booking_payments', ['bookable_type', 'bookable_id'])) {
                $table->index(['bookable_type', 'bookable_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            // Remove polymorphic columns
            $table->dropIndex(['bookable_type', 'bookable_id']);
            $table->dropColumn('bookable_type');
            $table->renameColumn('bookable_id', 'hotel_booking_id');
        });
        
        Schema::table('booking_payments', function (Blueprint $table) {
            // Re-add the foreign key
            $table->foreign('hotel_booking_id')->references('id')->on('hotel_bookings')->onDelete('cascade');
        });
    }
};
