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
        // Add category column to rooms table if it doesn't exist
        if (!Schema::hasColumn('rooms', 'category')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('category')->nullable()->after('hotel_id');
            });
        }
        
        // Migrate existing room type data to feature-based categories
        $this->migrateExistingRoomTypes();
        
        // Make category required after migration
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('category')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove category column
        if (Schema::hasColumn('rooms', 'category')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
    
    /**
     * Migrate existing traditional room type data to feature-based categories
     */
    private function migrateExistingRoomTypes()
    {
        // Mapping traditional room types to feature-based categories
        $typeMapping = [
            'single' => 'window_view',
            'double' => 'balcony_view',
            'twin' => 'window_view',
            'queen' => 'city_view',
            'king' => 'vip_access',
            'suite' => 'family_suite',
            'deluxe' => 'executive_lounge',
            'standard' => 'window_view',
            'premium' => 'sea_view',
            'executive' => 'executive_lounge',
            'family' => 'family_suite',
            'penthouse' => 'vip_access',
        ];
        
        // Get all rooms with room types
        $rooms = \DB::table('rooms')
            ->leftJoin('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->select('rooms.id', 'rooms.room_type_id', 'room_types.name as type_name', 'room_types.slug')
            ->whereNotNull('rooms.room_type_id')
            ->get();
            
        foreach ($rooms as $room) {
            $category = 'window_view'; // Default category
            
            // Map based on slug first, then name
            $identifier = strtolower($room->slug ?? $room->type_name ?? '');
            
            foreach ($typeMapping as $traditional => $featureBased) {
                if (str_contains($identifier, $traditional)) {
                    $category = $featureBased;
                    break;
                }
            }
            
            // Update the room with the new category
            \DB::table('rooms')
                ->where('id', $room->id)
                ->update(['category' => $category]);
        }
        
        // Set default category for rooms without room types
        \DB::table('rooms')
            ->whereNull('category')
            ->update(['category' => 'window_view']);
    }
};
