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
            // Add room_number_start and room_number_end columns if they don't exist
            if (!Schema::hasColumn('rooms', 'room_number_start')) {
                $table->integer('room_number_start')->nullable()->after('room_number');
            }
            
            if (!Schema::hasColumn('rooms', 'room_number_end')) {
                $table->integer('room_number_end')->nullable()->after('room_number_start');
            }
            
            // Ensure category column exists (it should from our previous migration)
            if (!Schema::hasColumn('rooms', 'category')) {
                $table->string('category')->nullable()->after('hotel_id');
            }
        });
        
        // Set default values for existing rooms
        \DB::table('rooms')->whereNull('room_number_start')->update([
            'room_number_start' => \DB::raw('CAST(room_number AS UNSIGNED)'),
            'room_number_end' => \DB::raw('CAST(room_number AS UNSIGNED)')
        ]);
        
        // Set default category for any rooms without categories
        \DB::table('rooms')->whereNull('category')->update([
            'category' => 'window_view'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['room_number_start', 'room_number_end']);
        });
    }
};
