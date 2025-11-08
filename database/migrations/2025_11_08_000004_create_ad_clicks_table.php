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
        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->id();
            
            // Ad relationship
            $table->foreignId('ad_id')->constrained('ads')->onDelete('cascade');
            
            // User tracking (optional - may be guest)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Session and request tracking
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Context
            $table->string('page_url', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('device_type', 50)->nullable(); // mobile, tablet, desktop
            $table->string('placement', 100)->nullable(); // where on page
            $table->string('destination_url', 500)->nullable(); // where click led to
            
            // Conversion tracking
            $table->boolean('converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['ad_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('session_id');
            $table->index('converted');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
    }
};
