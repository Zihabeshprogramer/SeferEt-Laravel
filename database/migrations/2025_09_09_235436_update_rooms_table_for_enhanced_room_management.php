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
        Schema::table('rooms', function (Blueprint $table) {
            // Make name field mandatory (if it exists, modify it; if not, add it)
            if (Schema::hasColumn('rooms', 'name')) {
                $table->string('name')->nullable(false)->change();
            } else {
                $table->string('name')->after('room_number');
            }
            
            // Add unique constraint for room_name within a hotel
            $table->unique(['hotel_id', 'name'], 'rooms_hotel_name_unique');
            
            // Add fields to support room number ranges
            $table->integer('room_number_start')->nullable()->after('room_number');
            $table->integer('room_number_end')->nullable()->after('room_number_start');
            
            // Add index for better performance
            $table->index(['hotel_id', 'room_number']);
            $table->index(['hotel_id', 'is_active', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Remove unique constraint
            $table->dropUnique('rooms_hotel_name_unique');
            
            // Remove new columns
            $table->dropColumn(['room_number_start', 'room_number_end']);
            
            // Drop indexes
            $table->dropIndex(['hotel_id', 'room_number']);
            $table->dropIndex(['hotel_id', 'is_active', 'is_available']);
            
            // Make name nullable again
            $table->string('name')->nullable()->change();
        });
    }
};
