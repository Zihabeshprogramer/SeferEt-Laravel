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
        Schema::table('flight_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('flight_bookings', 'offer_id')) {
                $table->unsignedBigInteger('offer_id')->nullable()->after('id');
                $table->foreign('offer_id')->references('id')->on('offers')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('flight_bookings', 'pnr')) {
                $table->string('pnr')->nullable()->after('confirmation_code')->index();
            }
            
            if (!Schema::hasColumn('flight_bookings', 'agent_id')) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('customer_id');
                $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            }
        });
        
        // Add composite unique constraint for PNR and agent (if both exist)
        // This prevents duplicate bookings from the same agent with the same PNR
        Schema::table('flight_bookings', function (Blueprint $table) {
            // Check if columns exist before adding unique constraint
            if (Schema::hasColumn('flight_bookings', 'pnr') && Schema::hasColumn('flight_bookings', 'agent_id')) {
                $table->unique(['pnr', 'agent_id'], 'unique_pnr_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('unique_pnr_agent');
            
            // Drop foreign keys
            if (Schema::hasColumn('flight_bookings', 'offer_id')) {
                $table->dropForeign(['offer_id']);
                $table->dropColumn('offer_id');
            }
            
            if (Schema::hasColumn('flight_bookings', 'agent_id')) {
                $table->dropForeign(['agent_id']);
                $table->dropColumn('agent_id');
            }
            
            if (Schema::hasColumn('flight_bookings', 'pnr')) {
                $table->dropColumn('pnr');
            }
        });
    }
};
