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
        Schema::create('amadeus_hotels_cache', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_id')->unique()->index();
            $table->string('name');
            $table->string('city_code', 10)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->integer('rating')->nullable();
            $table->string('offer_id')->nullable()->index();
            $table->json('offer_data')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amadeus_hotels_cache');
    }
};
