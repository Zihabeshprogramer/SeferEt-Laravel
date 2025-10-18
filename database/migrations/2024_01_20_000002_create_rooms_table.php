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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            
            // Hotel relationship
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->nullable()->constrained()->onDelete('set null');
            
            // Basic room information
            $table->string('room_number');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('size_sqm', 8, 2)->nullable();
            
            // Bed information
            $table->enum('bed_type', ['single', 'twin', 'double', 'queen', 'king', 'sofa_bed', 'bunk_bed'])->default('double');
            $table->tinyInteger('bed_count')->default(1);
            $table->tinyInteger('max_occupancy')->default(2);
            
            // Features & pricing
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->decimal('base_price', 10, 2);
            
            // Status
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            
            // Timestamps & soft delete
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['hotel_id', 'is_available']);
            $table->index(['hotel_id', 'is_active']);
            $table->unique(['hotel_id', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
