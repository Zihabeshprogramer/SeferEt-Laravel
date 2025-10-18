<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:check-roles {--update : Update partner roles to specific provider types}';

    /**
     * The console command description.
     */
    protected $description = 'Check user roles and optionally update partner roles to specific provider types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking current user roles...');
        $this->newLine();

        // Get all users with their Spatie roles
        $users = User::with('roles')->get(['id', 'name', 'email', 'role', 'service_type']);

        $this->info('📊 Current Users:');
        foreach ($users as $user) {
            $serviceType = $user->service_type ? " ({$user->service_type})" : '';
            $spatieRoles = $user->roles->pluck('name')->implode(', ');
            $spatieRoles = $spatieRoles ?: 'None';
            $this->line("  {$user->id}: {$user->name} - Role: {$user->role}{$serviceType} | Spatie: {$spatieRoles}");
        }

        $this->newLine();

        // Show role summary
        $roleCounts = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        $this->info('📈 Role Summary:');
        foreach ($roleCounts as $role => $count) {
            $this->line("   {$role}: {$count}");
        }

        $this->newLine();

        // Check if update is requested
        if ($this->option('update')) {
            $this->info('🔄 Updating partner roles based on service_type...');
            
            $updated = 0;

            // Update hotel providers
            $hotelUpdates = User::where('role', 'partner')
                ->where('service_type', 'hotel')
                ->update(['role' => 'hotel_provider']);
            
            if ($hotelUpdates > 0) {
                $this->info("  ✅ Updated {$hotelUpdates} users to hotel_provider");
                $updated += $hotelUpdates;
            }

            // Update transport providers
            $transportUpdates = User::where('role', 'partner')
                ->where('service_type', 'transport')
                ->update(['role' => 'transport_provider']);
            
            if ($transportUpdates > 0) {
                $this->info("  ✅ Updated {$transportUpdates} users to transport_provider");
                $updated += $transportUpdates;
            }

            // Update remaining partners to travel_agent
            $agentUpdates = User::where('role', 'partner')
                ->whereNull('service_type')
                ->orWhere('service_type', '')
                ->update(['role' => 'travel_agent']);
            
            if ($agentUpdates > 0) {
                $this->info("  ✅ Updated {$agentUpdates} users to travel_agent");
                $updated += $agentUpdates;
            }

            if ($updated === 0) {
                $this->info('  ℹ️  No users needed role updates');
            } else {
                $this->info("  🎉 Updated {$updated} user roles total");
                
                // Show updated summary
                $this->newLine();
                $this->info('📈 Updated Role Summary:');
                $newRoleCounts = User::selectRaw('role, COUNT(*) as count')
                    ->groupBy('role')
                    ->pluck('count', 'role');

                foreach ($newRoleCounts as $role => $count) {
                    $this->line("   {$role}: {$count}");
                }
            }
        } else {
            $this->newLine();
            $this->comment('💡 To update partner roles automatically, run: php artisan users:check-roles --update');
        }

        return 0;
    }
}