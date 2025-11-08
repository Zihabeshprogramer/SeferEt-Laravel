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
        Schema::create('featured_requests', function (Blueprint $table) {
            $table->id();
            
            // Product polymorphic relationship
            $table->unsignedBigInteger('product_id');
            $table->string('product_type'); // 'flight', 'hotel', 'package'
            
            // Request information
            $table->unsignedBigInteger('requested_by'); // User who requested
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Approval information
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Feature settings
            $table->integer('priority_level')->default(1)->comment('Higher priority = shown first');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'product_type']);
            $table->index('requested_by');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            
            // Foreign keys
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_requests');
    }
};
