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
        Schema::create('package_flight', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->enum('flight_type', ['outbound', 'return', 'connecting']);
            $table->boolean('is_required')->default(true);
            $table->decimal('markup_percentage', 5, 2)->default(0.00);
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->integer('seats_allocated')->default(0);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate package-flight associations
            $table->unique(['package_id', 'flight_id', 'flight_type']);
            
            // Indexes for performance
            $table->index('package_id');
            $table->index('flight_id');
            $table->index('flight_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_flight');
    }
};
