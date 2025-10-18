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
        Schema::table('pricing_rules', function (Blueprint $table) {
            // Add room_category column
            $table->string('room_category')->nullable()->after('room_type_id');
        });
        
        // Migrate existing room_type_id data to room_category if needed
        // This is optional since pricing rules might work differently
        // $this->migrateRoomTypesToCategories();
        
        // Drop room_type_id foreign key and column if it exists
        Schema::table('pricing_rules', function (Blueprint $table) {
            if (Schema::hasColumn('pricing_rules', 'room_type_id')) {
                try {
                    $table->dropForeign(['room_type_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('room_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            // Re-add room_type_id column
            $table->foreignId('room_type_id')->nullable()->after('hotel_id')->constrained('room_types')->onDelete('set null');
            // Remove room_category column
            $table->dropColumn('room_category');
        });
    }
    
    /**
     * Migrate room type IDs to categories (optional)
     */
    private function migrateRoomTypesToCategories()
    {
        // Get all pricing rules with room types and convert them
        $rules = \DB::table('pricing_rules')
            ->leftJoin('room_types', 'pricing_rules.room_type_id', '=', 'room_types.id')
            ->whereNotNull('pricing_rules.room_type_id')
            ->select('pricing_rules.id', 'room_types.name', 'room_types.slug')
            ->get();
            
        foreach ($rules as $rule) {
            // Simple mapping - you might want to customize this
            $category = 'window_view'; // Default
            
            // Basic mapping logic
            $identifier = strtolower($rule->slug ?? $rule->name ?? '');
            if (str_contains($identifier, 'deluxe')) {
                $category = 'executive_lounge';
            } elseif (str_contains($identifier, 'suite')) {
                $category = 'family_suite';
            } elseif (str_contains($identifier, 'premium')) {
                $category = 'sea_view';
            }
            
            \DB::table('pricing_rules')
                ->where('id', $rule->id)
                ->update(['room_category' => $category]);
        }
    }
};
