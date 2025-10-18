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
        // Add approval tracking fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'approval_notes')) {
                $table->text('approval_notes')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable();
                $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'suspended_by')) {
                $table->unsignedBigInteger('suspended_by')->nullable();
                $table->foreign('suspended_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'reactivated_by')) {
                $table->unsignedBigInteger('reactivated_by')->nullable();
                $table->foreign('reactivated_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'reactivated_at')) {
                $table->timestamp('reactivated_at')->nullable();
            }
            
            // Role change tracking
            if (!Schema::hasColumn('users', 'role_changed_by')) {
                $table->unsignedBigInteger('role_changed_by')->nullable();
                $table->foreign('role_changed_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'role_changed_at')) {
                $table->timestamp('role_changed_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'role_change_reason')) {
                $table->text('role_change_reason')->nullable();
            }
        });
        
        // Add approval tracking fields to hotel_services table
        Schema::table('hotel_services', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_services', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('hotel_services', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            
            if (!Schema::hasColumn('hotel_services', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable();
                $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('hotel_services', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
            
            if (!Schema::hasColumn('hotel_services', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            
            if (!Schema::hasColumn('hotel_services', 'suspended_by')) {
                $table->unsignedBigInteger('suspended_by')->nullable();
                $table->foreign('suspended_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('hotel_services', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove approval tracking fields from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('users', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('users', 'approval_notes')) {
                $table->dropColumn('approval_notes');
            }
            if (Schema::hasColumn('users', 'rejected_by')) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('users', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('users', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('users', 'suspended_by')) {
                $table->dropForeign(['suspended_by']);
                $table->dropColumn('suspended_by');
            }
            if (Schema::hasColumn('users', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
            if (Schema::hasColumn('users', 'suspension_reason')) {
                $table->dropColumn('suspension_reason');
            }
            if (Schema::hasColumn('users', 'reactivated_by')) {
                $table->dropForeign(['reactivated_by']);
                $table->dropColumn('reactivated_by');
            }
            if (Schema::hasColumn('users', 'reactivated_at')) {
                $table->dropColumn('reactivated_at');
            }
            if (Schema::hasColumn('users', 'role_changed_by')) {
                $table->dropForeign(['role_changed_by']);
                $table->dropColumn('role_changed_by');
            }
            if (Schema::hasColumn('users', 'role_changed_at')) {
                $table->dropColumn('role_changed_at');
            }
            if (Schema::hasColumn('users', 'role_change_reason')) {
                $table->dropColumn('role_change_reason');
            }
        });
        
        // Remove approval tracking fields from hotel_services table
        Schema::table('hotel_services', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_services', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('hotel_services', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('hotel_services', 'rejected_by')) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('hotel_services', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('hotel_services', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('hotel_services', 'suspended_by')) {
                $table->dropForeign(['suspended_by']);
                $table->dropColumn('suspended_by');
            }
            if (Schema::hasColumn('hotel_services', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
        });
    }
};
