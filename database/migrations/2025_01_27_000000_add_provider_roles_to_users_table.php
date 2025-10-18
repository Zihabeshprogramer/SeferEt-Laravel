<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the role enum to include provider types
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'partner', 'customer', 'hotel_provider', 'transport_provider', 'travel_agent') DEFAULT 'customer'");
        
        // Also add a travel_agent role which might be needed
        // The system seems to use both 'partner' and 'travel_agent'
        
        // Update existing 'partner' users to 'travel_agent' if they don't have a specific provider type
        DB::statement("UPDATE users SET role = 'travel_agent' WHERE role = 'partner' AND service_type IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to original enum
        DB::statement("UPDATE users SET role = 'partner' WHERE role IN ('hotel_provider', 'transport_provider', 'travel_agent')");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'partner', 'customer') DEFAULT 'customer'");
    }
};