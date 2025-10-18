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
        Schema::create('hotel_reviews', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Review content
            $table->tinyInteger('rating'); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('comment');
            
            // Status
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_published')->default(true);
            
            // Hotel response
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            // Timestamps & soft delete
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['hotel_id', 'is_published']);
            $table->index(['customer_id']);
            $table->index(['rating', 'is_published']);
            $table->unique(['hotel_booking_id', 'customer_id']); // One review per booking per customer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_reviews');
    }
};
