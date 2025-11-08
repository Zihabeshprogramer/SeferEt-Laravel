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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->enum('offer_source', ['local', 'amadeus', 'manual'])->default('local');
            $table->string('external_offer_id')->nullable()->index();
            $table->string('offer_hash')->unique();
            $table->string('origin', 10)->index();
            $table->string('destination', 10)->index();
            $table->date('departure_date')->index();
            $table->date('return_date')->nullable();
            $table->decimal('price_amount', 10, 2);
            $table->string('price_currency', 5)->default('USD');
            $table->json('segments')->nullable();
            $table->unsignedBigInteger('owner_agent_id')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('owner_agent_id')->references('id')->on('users')->onDelete('set null');
            
            // Composite indexes for common queries
            $table->index(['offer_source', 'departure_date']);
            $table->index(['origin', 'destination', 'departure_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
