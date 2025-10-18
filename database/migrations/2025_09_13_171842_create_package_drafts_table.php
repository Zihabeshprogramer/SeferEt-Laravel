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
        Schema::create('package_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->longText('draft_data'); // JSON data for the draft
            $table->integer('current_step')->default(1);
            $table->json('step_status')->nullable()->comment('JSON of step completion status');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'is_expired']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_drafts');
    }
};
