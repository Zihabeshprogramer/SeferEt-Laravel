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
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->onDelete('cascade');
            $table->foreignId('allocation_id')->nullable()->constrained('allocations')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('primary_driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('secondary_driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional trip details
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance (with custom short names to avoid MySQL 64-char limit)
            $table->index(['vehicle_id', 'start_date', 'end_date'], 'va_vehicle_dates_idx');
            $table->index(['primary_driver_id', 'start_date', 'end_date'], 'va_primary_dates_idx');
            $table->index(['secondary_driver_id', 'start_date', 'end_date'], 'va_secondary_dates_idx');
            $table->index(['provider_id'], 'va_provider_idx');
            $table->index(['status'], 'va_status_idx');
            $table->index(['service_request_id'], 'va_service_request_idx');
            $table->index(['allocation_id'], 'va_allocation_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_assignments');
    }
};
