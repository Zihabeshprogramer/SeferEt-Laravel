<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for travel agents
        $travelAgentPermissions = [
            'manage packages',
            'create packages',
            'edit packages',
            'delete packages',
            'view packages',
            'manage flights',
            'create flights',
            'edit flights',
            'delete flights',
            'view flights',
            'manage bookings',
            'view bookings',
            'manage customers',
            'view customers',
            'view commissions',
            'view reports',
        ];

        // Create permissions for hotel providers
        $hotelProviderPermissions = [
            'manage hotels',
            'create hotels',
            'edit hotels',
            'delete hotels',
            'view hotels',
            'manage rooms',
            'create rooms',
            'edit rooms',
            'delete rooms',
            'view rooms',
            'manage hotel bookings',
            'view hotel bookings',
            'manage rates',
            'view rates',
            'manage availability',
            'view hotel reports',
        ];

        // Create permissions for transport providers
        $transportProviderPermissions = [
            'manage transport services',
            'create transport services',
            'edit transport services',
            'delete transport services',
            'view transport services',
            'manage transport rates',
            'view transport rates',
            'manage transport bookings',
            'view transport bookings',
            'manage fleet',
            'view transport reports',
        ];

        // Create permissions for admins
        $adminPermissions = [
            'manage users',
            'create users',
            'edit users',
            'delete users',
            'view users',
            'manage partners',
            'approve partners',
            'view admin dashboard',
            'manage system settings',
            // Hotel approval permissions
            'view hotel approvals',
            'approve hotel providers',
            'reject hotel providers',
            'suspend hotel providers',
            'approve hotel services',
            'reject hotel services',
            'suspend hotel services',
            'manage hotel provider roles',
            'bulk approve hotels',
            'view hotel approval logs',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $travelAgentPermissions,
            $hotelProviderPermissions,
            $transportProviderPermissions,
            $adminPermissions
        );

        // Create permissions if they don't exist
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $travelAgentRole = Role::firstOrCreate(['name' => 'travel_agent', 'guard_name' => 'web']);
        $hotelProviderRole = Role::firstOrCreate(['name' => 'hotel_provider', 'guard_name' => 'web']);
        $transportProviderRole = Role::firstOrCreate(['name' => 'transport_provider', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Assign permissions to roles
        $travelAgentRole->syncPermissions($travelAgentPermissions);
        $hotelProviderRole->syncPermissions($hotelProviderPermissions);
        $transportProviderRole->syncPermissions($transportProviderPermissions);
        $adminRole->syncPermissions($adminPermissions);
        $superAdminRole->syncPermissions($allPermissions); // Super admin gets all permissions

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: travel_agent, hotel_provider, transport_provider, admin, super_admin');
        $this->command->info('Total permissions created: ' . count($allPermissions));
    }
}
