<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixAdminPermissions extends Command
{
    protected $signature = 'fix:admin-permissions {user_id=1}';
    protected $description = 'Fix admin permissions for a user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User ID {$userId} not found!");
            return 1;
        }

        $this->info("=== User Information ===");
        $this->info("Name: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Role Column: {$user->role}");
        $this->info("Status: {$user->status}");

        $this->info("\n=== Role Checks ===");
        $this->info("isAdmin(): " . ($user->isAdmin() ? 'YES ✓' : 'NO ✗'));
        $this->info("hasRole('admin'): " . ($user->hasRole('admin') ? 'YES ✓' : 'NO ✗'));
        $this->info("hasRole('super_admin'): " . ($user->hasRole('super_admin') ? 'YES ✓' : 'NO ✗'));

        $this->info("\n=== Spatie Roles ===");
        $spatieRoles = $user->getRoleNames();
        if ($spatieRoles->isEmpty()) {
            $this->warn("No Spatie roles assigned!");
        } else {
            foreach ($spatieRoles as $role) {
                $this->info("- {$role}");
            }
        }

        $this->info("\n=== Available Spatie Roles ===");
        $availableRoles = Role::all();
        foreach ($availableRoles as $role) {
            $this->info("- {$role->name} (ID: {$role->id})");
        }

        // Fix options
        if ($this->confirm("\nDo you want to ensure admin role column is set?", true)) {
            $user->role = User::ROLE_ADMIN;
            $user->save();
            $this->info("✓ Role column set to 'admin'");
        }

        if ($this->confirm("Do you want to sync Spatie 'admin' role?", true)) {
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            
            // Remove all roles first, then assign admin
            $user->syncRoles(['admin']);
            
            $this->info("✓ Spatie 'admin' role synced");
        }

        if ($this->confirm("Do you want to also add 'super_admin' role?", true)) {
            $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $user->assignRole('super_admin');
            $this->info("✓ Spatie 'super_admin' role added");
        }

        if ($this->confirm("Do you want to ensure status is 'active'?", true)) {
            $user->status = User::STATUS_ACTIVE;
            $user->save();
            $this->info("✓ Status set to 'active'");
        }

        // Final verification
        $user->refresh();
        $this->info("\n=== Final Verification ===");
        $this->info("Role Column: {$user->role}");
        $this->info("Status: {$user->status}");
        $this->info("isAdmin(): " . ($user->isAdmin() ? 'YES ✓' : 'NO ✗'));
        $this->info("hasRole('admin'): " . ($user->hasRole('admin') ? 'YES ✓' : 'NO ✗'));
        $this->info("Spatie Roles: " . $user->getRoleNames()->implode(', '));

        $this->newLine();
        $this->info("✅ Admin permissions fixed successfully!");

        return 0;
    }
}
