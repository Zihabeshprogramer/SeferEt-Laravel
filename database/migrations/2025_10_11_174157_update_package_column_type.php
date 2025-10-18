<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 'cultural','adventure','leisure','business','family','luxury','budget','honeymoon','religious','wellness'
     */
    public function up(): void
    {

        // Step 1: Convert ENUM to VARCHAR (to allow data cleanup)
        Schema::table('packages', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });

        // Step 2: Clean up old/invalid values
        DB::statement("
            UPDATE packages
            SET type = 'budget'
            WHERE type NOT IN (
                'cultural','adventure','leisure','business','family','luxury','budget','honeymoon','religious','wellness'
            )
            OR type IS NULL
            OR type = ''
        ");

        // Step 3: Change column back to ENUM with new allowed values
        DB::statement("
            ALTER TABLE packages 
            MODIFY COLUMN type ENUM(
                'cultural','adventure','leisure','business','family','luxury','budget','honeymoon','religious','wellness'
            ) 
            NOT NULL DEFAULT 'budget'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("
            ALTER TABLE packages 
            MODIFY COLUMN type ENUM('economy', 'standard', 'premium', 'luxury') 
            NOT NULL DEFAULT 'standard'
        ");
    }
};
