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
            // Device and placement targeting
            $table->enum('device_type', ['all', 'mobile', 'tablet', 'desktop'])->default('all')->after('is_active');
            $table->string('placement', 100)->nullable()->after('device_type')->comment('home_top, package_list, etc.');
            
            // Region targeting
            $table->json('regions')->nullable()->after('placement')->comment('Array of targeted countries/regions');
            
            // Tracking counts (denormalized for performance)
            $table->unsignedBigInteger('impressions_count')->default(0)->after('analytics_meta');
            $table->unsignedBigInteger('clicks_count')->default(0)->after('impressions_count');
            $table->decimal('ctr', 5, 2)->default(0)->after('clicks_count')->comment('Click-through rate percentage');
            
            // Budget and limits
            $table->unsignedInteger('max_impressions')->nullable()->after('ctr');
            $table->unsignedInteger('max_clicks')->nullable()->after('max_impressions');
            $table->decimal('budget', 10, 2)->nullable()->after('max_clicks');
            $table->decimal('spent', 10, 2)->default(0)->after('budget');
            
            // Local owner prioritization flag
            $table->boolean('is_local_owner')->default(false)->after('priority')->comment('Boost local partner ads');
            
            // Admin notes
            $table->text('admin_notes')->nullable()->after('rejection_reason');
            
            // Additional indexes
            $table->index(['device_type', 'placement', 'status']);
            $table->index(['is_local_owner', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['device_type', 'placement', 'status']);
            $table->dropIndex(['is_local_owner', 'priority']);
            
            $table->dropColumn([
                'device_type',
                'placement',
                'regions',
                'impressions_count',
                'clicks_count',
                'ctr',
                'max_impressions',
                'max_clicks',
                'budget',
                'spent',
                'is_local_owner',
                'admin_notes'
            ]);
        });
    }
};
