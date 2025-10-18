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
        Schema::table('packages', function (Blueprint $table) {
            $table->json('images')->nullable()->after('service_preferences');
            $table->boolean('is_featured')->default(false)->after('images');
            $table->integer('max_participants')->nullable()->after('is_featured');
            $table->date('available_from')->nullable()->after('max_participants');
            $table->date('available_until')->nullable()->after('available_from');
            $table->json('departure_cities')->nullable()->after('available_until');
            $table->boolean('requires_approval')->default(false)->after('departure_cities');
            
            // Add indexes
            $table->index('is_featured');
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['available_from', 'available_until']);
            
            $table->dropColumn([
                'images',
                'is_featured',
                'max_participants',
                'available_from',
                'available_until',
                'departure_cities',
                'requires_approval'
            ]);
        });
    }
};
