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
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            
            // Rate details
            $table->date('date');
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['room_id', 'date']);
            $table->index(['room_id', 'is_active']);
            $table->unique(['room_id', 'date']); // One rate per room per date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_rates');
    }
};
