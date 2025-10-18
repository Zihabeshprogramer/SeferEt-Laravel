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
        Schema::create('flight_collaborations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('collaborator_agent_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'active', 'completed', 'cancelled'])->default('pending');
            $table->decimal('commission_percentage', 5, 2)->default(5.00);
            $table->integer('allocated_seats')->default(0);
            $table->integer('booked_seats')->default(0);
            $table->decimal('total_commission_earned', 10, 2)->default(0);
            $table->text('invitation_message')->nullable();
            $table->text('response_message')->nullable();
            $table->datetime('invited_at');
            $table->datetime('responded_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->json('terms_agreed')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['flight_id', 'status']);
            $table->index(['owner_agent_id', 'status']);
            $table->index(['collaborator_agent_id', 'status']);
            $table->index(['status', 'expires_at']);
            
            // Prevent duplicate collaborations
            $table->unique(['flight_id', 'collaborator_agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_collaborations');
    }
};
