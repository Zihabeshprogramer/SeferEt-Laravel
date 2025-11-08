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
        Schema::create('ad_audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Ad relationship
            $table->unsignedBigInteger('ad_id');
            
            // Event information
            $table->string('event_type', 50)->comment('created, submitted, approved, rejected, updated, deleted');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User who performed the action');
            
            // Change tracking
            $table->json('changes')->nullable()->comment('Before/after changes for updates');
            $table->json('metadata')->nullable()->comment('Additional context: IP, user agent, etc.');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('ad_id');
            $table->index('event_type');
            $table->index('user_id');
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_audit_logs');
    }
};
