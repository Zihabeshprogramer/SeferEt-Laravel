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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->enum('maintenance_type', ['routine', 'repair', 'inspection', 'emergency', 'other']);
            $table->date('maintenance_date');
            $table->date('next_due_date')->nullable();
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('service_provider')->nullable(); // Garage/mechanic name
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->json('documents')->nullable(); // Receipts, reports, etc.
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['vehicle_id']);
            $table->index(['provider_id']);
            $table->index(['status']);
            $table->index(['maintenance_date']);
            $table->index(['next_due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
