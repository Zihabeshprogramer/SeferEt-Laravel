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
        Schema::create('ad_analytics_daily', function (Blueprint $table) {
            $table->id();
            
            // Ad reference
            $table->foreignId('ad_id')->constrained('ads')->onDelete('cascade');
            
            // Date for this analytics record
            $table->date('date');
            
            // Aggregated metrics
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->default(0); // Click-through rate percentage
            $table->unsignedBigInteger('unique_users')->default(0);
            $table->unsignedBigInteger('unique_sessions')->default(0);
            
            // Conversions
            $table->unsignedBigInteger('conversions')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            
            // Device breakdown
            $table->json('device_breakdown')->nullable(); // {mobile: 120, tablet: 30, desktop: 50}
            
            // Placement breakdown
            $table->json('placement_breakdown')->nullable(); // {home: 100, search: 50, details: 50}
            
            // Performance metrics
            $table->decimal('avg_position', 5, 2)->nullable(); // Average display position
            $table->unsignedInteger('total_display_time_seconds')->default(0); // Total time ad was displayed
            
            // Cost metrics (if applicable)
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('cpc', 10, 2)->default(0); // Cost per click
            $table->decimal('cpm', 10, 2)->default(0); // Cost per mille (thousand impressions)
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->unique(['ad_id', 'date']); // Ensure one record per ad per day
            $table->index('date');
            $table->index(['ad_id', 'date']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_analytics_daily');
    }
};
