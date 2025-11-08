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
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Type of favorite item (flight, hotel, package)
            $table->enum('type', ['flight', 'hotel', 'package'])->index();
            
            // Store the favorite item data as JSON for flexibility
            // This allows us to store different data structures for different types
            $table->json('item_data');
            
            // Optional reference to actual records if they exist in the database
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->string('reference_table')->nullable();
            
            // Metadata
            $table->string('title')->nullable(); // Human readable title for the favorite
            $table->text('notes')->nullable(); // User's notes about this favorite
            $table->boolean('is_available')->default(true); // Whether the item is still available
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            
            // Ensure user can't have duplicate favorites of the same item
            $table->unique(['user_id', 'type', 'reference_id'], 'unique_user_favorite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};