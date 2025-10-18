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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Standard Laravel Authentication Fields
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Role and Status Management
            $table->enum('role', ['admin', 'partner', 'customer'])->default('customer');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->text('suspend_reason')->nullable();
            
            // Contact Information
            $table->string('phone', 20)->nullable();
            
            // Partner-Specific Fields (nullable for STI)
            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->string('contact_phone', 20)->nullable();
            
            // Additional Profile Fields for Enhanced User Management
            $table->string('avatar')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('nationality', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            
            // Business/Partner Additional Fields
            $table->text('company_description')->nullable();
            $table->string('business_license')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // Store social media links as JSON
            
            // Umrah-Specific Fields
            $table->boolean('has_umrah_experience')->default(false);
            $table->integer('completed_umrah_count')->default(0);
            $table->text('special_requirements')->nullable(); // Dietary, medical, accessibility needs
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            
            // Admin/System Fields
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->json('preferences')->nullable(); // Store user preferences as JSON
            $table->text('notes')->nullable(); // Admin notes about the user
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Soft deletes for data integrity
            
            // Indexes for performance
            $table->index(['role', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('last_login_at');
            $table->index(['role', 'company_name']); // For partner searches
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
