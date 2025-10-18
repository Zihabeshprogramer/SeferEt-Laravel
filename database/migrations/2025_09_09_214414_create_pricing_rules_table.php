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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('rule_type', ['seasonal', 'advance_booking', 'length_of_stay', 'day_of_week', 'occupancy', 'promotional', 'blackout', 'minimum_stay']);
            $table->foreignId('hotel_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('adjustment_type', ['percentage', 'fixed', 'multiply']);
            $table->decimal('adjustment_value', 10, 2);
            $table->integer('min_nights')->nullable();
            $table->integer('max_nights')->nullable();
            $table->json('days_of_week')->nullable(); // ['monday', 'tuesday', etc.]
            $table->integer('priority')->default(5); // 1-10 scale
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // Additional conditions
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['hotel_id', 'room_type_id']);
            $table->index(['start_date', 'end_date']);
            $table->index(['is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
