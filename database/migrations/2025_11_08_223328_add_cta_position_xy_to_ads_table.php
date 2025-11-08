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
        Schema::table('ads', function (Blueprint $table) {
            $table->decimal('cta_position_x', 5, 2)->default(50)->after('cta_position')->comment('CTA X position in percentage');
            $table->decimal('cta_position_y', 5, 2)->default(50)->after('cta_position_x')->comment('CTA Y position in percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['cta_position_x', 'cta_position_y']);
        });
    }
};
