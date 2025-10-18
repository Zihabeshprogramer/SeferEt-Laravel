<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Make package_id nullable for draft package support
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // First, drop the foreign key constraint
            $table->dropForeign(['package_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('package_id')->nullable()->change();
            
            // Add the foreign key back with the nullable constraint
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['package_id']);
            
            // Make the column not nullable again
            $table->unsignedBigInteger('package_id')->nullable(false)->change();
            
            // Add the foreign key back
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }
};