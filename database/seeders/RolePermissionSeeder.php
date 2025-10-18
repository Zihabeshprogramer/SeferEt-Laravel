<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache before creating roles/permissions
        app()['cache']->forget('spatie.permission.cache');
        
        // Fresh start - sync permissions for super admin
        $existingAdmin = User::where('email', 'admin@seferet.com')->first();
        if ($existingAdmin) {
            $existingAdmin->syncRoles([]);
        }
        
        // Create permissions
        $permissions = [
            // Admin permissions
            'manage users',
            'verify users', 
            'suspend users',
            'reject users',
            'activate users',
            'manage packages',
            'verify packages',
            'create admin users',
            'view admin dashboard',
            'manage roles',
            'assign roles',
            
            // Partner management permissions
            'manage partners',
            'approve partners',
            'view partners',
            'suspend partners',
            'reactivate partners',
            
            // Travel Agent permissions
            'create packages',
            'edit own packages',
            'view own packages',
            'view package bookings',
            'manage bookings',
            'view earnings',
            'view travel dashboard',
            
            // Hotel Provider permissions
            'manage hotels',
            'manage rooms',
            'manage hotel pricing',
            'manage hotel availability',
            'create hotel deals',
            'view hotel bookings',
            'view hotel earnings',
            'view hotel dashboard',
            
            // Transport Provider permissions
            'manage routes',
            'manage fleet',
            'manage drivers',
            'manage transport availability',
            'view transport bookings',
            'view transport earnings',
            'view transport dashboard',
            
            // Common partner permissions
            'manage profile',
            'view financial reports',
            'access support',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        $userVerifier = Role::firstOrCreate(['name' => 'user_verifier']);
        $userVerifier->syncPermissions([
            'manage users', 
            'verify users', 
            'suspend users', 
            'reject users', 
            'activate users',
            'view admin dashboard'
        ]);

        $packageVerifier = Role::firstOrCreate(['name' => 'package_verifier']);
        $packageVerifier->syncPermissions([
            'manage packages', 
            'verify packages',
            'view admin dashboard'
        ]);
        
        // Create B2B Partner Roles
        $travelAgent = Role::firstOrCreate(['name' => 'travel_agent']);
        $travelAgent->syncPermissions([
            'create packages',
            'edit own packages',
            'view own packages',
            'view package bookings',
            'manage bookings',
            'view earnings',
            'view travel dashboard',
            'manage profile',
            'view financial reports',
            'access support'
        ]);
        
        $hotelProvider = Role::firstOrCreate(['name' => 'hotel_provider']);
        $hotelProvider->syncPermissions([
            'manage hotels',
            'manage rooms',
            'manage hotel pricing',
            'manage hotel availability',
            'create hotel deals',
            'view hotel bookings',
            'view hotel earnings',
            'view hotel dashboard',
            'manage profile',
            'view financial reports',
            'access support'
        ]);
        
        $transportProvider = Role::firstOrCreate(['name' => 'transport_provider']);
        $transportProvider->syncPermissions([
            'manage routes',
            'manage fleet',
            'manage drivers',
            'manage transport availability',
            'view transport bookings',
            'view transport earnings',
            'view transport dashboard',
            'manage profile',
            'view financial reports',
            'access support'
        ]);
        
        // Create default super admin user if it doesn't exist
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@seferet.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
        
        $superAdminUser->syncRoles(['super_admin']);
        
        // Create user verifier admin (single role)
        $userVerifier = User::firstOrCreate(
            ['email' => 'verifier@seferet.com'],
            [
                'name' => 'User Verifier Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
        $userVerifier->syncRoles(['user_verifier']);
        
        // Create package verifier admin (single role)
        $packageVerifier = User::firstOrCreate(
            ['email' => 'package.verifier@seferet.com'],
            [
                'name' => 'Package Verifier Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
        $packageVerifier->syncRoles(['package_verifier']);
        
        // Create multi-role admin (demonstrates multiple permission roles)
        $multiRoleAdmin = User::firstOrCreate(
            ['email' => 'multi.admin@seferet.com'],
            [
                'name' => 'Multi-Role Admin',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
        $multiRoleAdmin->syncRoles(['user_verifier', 'package_verifier']); // Multiple roles
        
        // Create demo B2B partner users
        $travelAgentUser = User::firstOrCreate(
            ['email' => 'travel.agent@example.com'],
            [
                'name' => 'Mecca Travel Agency',
                'password' => bcrypt('password'),
                'role' => User::ROLE_PARTNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'company_name' => 'Mecca Travel & Tours',
                'company_registration_number' => 'TRV001',
                'contact_phone' => '+966501234567'
            ]
        );
        $travelAgentUser->syncRoles(['travel_agent']);
        
        $hotelProviderUser = User::firstOrCreate(
            ['email' => 'hotel.provider@example.com'],
            [
                'name' => 'Al-Haram Hotels Group',
                'password' => bcrypt('password'),
                'role' => User::ROLE_HOTEL_PROVIDER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'company_name' => 'Al-Haram Luxury Hotels',
                'company_registration_number' => 'HTL001',
                'contact_phone' => '+966502345678'
            ]
        );
        $hotelProviderUser->syncRoles(['hotel_provider']);
        
        $transportProviderUser = User::firstOrCreate(
            ['email' => 'transport.provider@example.com'],
            [
                'name' => 'Medina Transport Co.',
                'password' => bcrypt('password'),
                'role' => User::ROLE_TRANSPORT_PROVIDER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'company_name' => 'Medina Express Transport',
                'company_registration_number' => 'TRP001',
                'contact_phone' => '+966503456789'
            ]
        );
        $transportProviderUser->syncRoles(['transport_provider']);
        
        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('');
        $this->command->info('Admin users created:');
        $this->command->info('- Super Admin: admin@seferet.com / password123');
        $this->command->info('- User Verifier: verifier@seferet.com / password');
        $this->command->info('- Package Verifier: package.verifier@seferet.com / password');
        $this->command->info('- Multi-Role Admin: multi.admin@seferet.com / password (has both user & package roles)');
        $this->command->info('');
        $this->command->info('B2B Partner users created:');
        $this->command->info('- Travel Agent: travel.agent@example.com / password');
        $this->command->info('- Hotel Provider: hotel.provider@example.com / password');
        $this->command->info('- Transport Provider: transport.provider@example.com / password');
    }
}
