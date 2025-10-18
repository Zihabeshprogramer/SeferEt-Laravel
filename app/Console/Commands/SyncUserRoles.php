<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SyncUserRoles extends Command
{
    protected $signature = 'users:sync-spatie-roles {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Sync users\' Spatie roles with their role column';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN - No changes will be made');
        } else {
            $this->info('🔄 Syncing Spatie roles with role column...');
        }
        
        $this->newLine();

        // Get all users
        $users = User::with('roles')->get(['id', 'name', 'email', 'role']);
        
        $rolesNeedingSpatie = ['admin', 'travel_agent', 'hotel_provider', 'transport_provider'];
        $syncCount = 0;
        
        foreach ($users as $user) {
            // Skip customers - they don't need Spatie roles for basic functionality
            if ($user->role === 'customer') {
                continue;
            }
            
            // Check if user needs Spatie role
            if (in_array($user->role, $rolesNeedingSpatie)) {
                $currentSpatieRoles = $user->roles->pluck('name')->toArray();
                
                // Check if user already has the correct Spatie role
                if (!in_array($user->role, $currentSpatieRoles)) {
                    $this->line("👤 {$user->name} (ID: {$user->id})");
                    $this->line("   Role column: {$user->role}");
                    $this->line("   Current Spatie roles: " . (empty($currentSpatieRoles) ? 'None' : implode(', ', $currentSpatieRoles)));
                    
                    if (!$dryRun) {
                        // Ensure the role exists in Spatie
                        $spatieRole = Role::firstOrCreate(['name' => $user->role]);
                        
                        // Assign the role
                        $user->assignRole($user->role);
                        
                        $this->line("   ✅ Assigned Spatie role: {$user->role}");
                    } else {
                        $this->line("   🔄 Would assign Spatie role: {$user->role}");
                    }
                    
                    $syncCount++;
                    $this->newLine();
                }
            }
        }
        
        if ($syncCount === 0) {
            $this->info('✨ All users already have correct Spatie roles!');
        } else {
            if ($dryRun) {
                $this->info("📊 {$syncCount} users would be updated");
                $this->comment('💡 Run without --dry-run to apply changes');
            } else {
                $this->info("🎉 Successfully synced {$syncCount} users");
            }
        }
        
        return 0;
    }
}