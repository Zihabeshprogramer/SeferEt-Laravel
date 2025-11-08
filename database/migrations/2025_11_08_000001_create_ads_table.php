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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            
            // Owner polymorphic relationship (who created the ad)
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type'); // App\Models\User, etc.
            
            // Product polymorphic relationship (what the ad is promoting)
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_type')->nullable(); // 'hotel', 'package', 'flight', 'offer', etc.
            
            // Ad content
            $table->string('title', 255);
            $table->text('description')->nullable();
            
            // Image fields
            $table->string('image_path')->nullable()->comment('Original/main image path');
            $table->json('image_variants')->nullable()->comment('Responsive image variants with sizes');
            
            // Call-to-action configuration
            $table->string('cta_text', 100)->nullable()->comment('CTA button text');
            $table->string('cta_action', 500)->nullable()->comment('CTA action URL or deep link');
            $table->decimal('cta_position', 3, 2)->default(0.50)->comment('Normalized position 0-1');
            $table->string('cta_style', 50)->default('primary')->comment('Button style: primary, secondary, etc.');
            
            // Approval workflow
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Scheduling
            $table->timestamp('start_at')->nullable()->comment('When ad becomes active');
            $table->timestamp('end_at')->nullable()->comment('When ad expires');
            
            // Priority and display
            $table->integer('priority')->default(0)->comment('Higher priority ads shown first');
            $table->boolean('is_active')->default(true)->comment('Manual active/inactive toggle');
            
            // Analytics metadata
            $table->json('analytics_meta')->nullable()->comment('UTM params, tracking IDs, etc.');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['owner_id', 'owner_type']);
            $table->index(['product_id', 'product_type']);
            $table->index('status');
            $table->index(['start_at', 'end_at']);
            $table->index('priority');
            $table->index('is_active');
            
            // Foreign keys
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
