<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test customers
        $customer1 = User::create([
            'name' => 'Ahmad Ali',
            'email' => 'ahmad.customer@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        
        $customer2 = User::create([
            'name' => 'Fatima Hassan',
            'email' => 'fatima.customer@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_PENDING,
            'email_verified_at' => now(),
        ]);
        
        // Create test partners
        $partner1 = User::create([
            'name' => 'Ibrahim Travel Agency',
            'email' => 'ibrahim.partner@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_PARTNER,
            'status' => User::STATUS_PENDING,
            'company_name' => 'Ibrahim Travel Services',
            'company_registration_number' => 'TR123456',
            'contact_phone' => '+1234567890',
            'email_verified_at' => now(),
        ]);
        
        $partner2 = User::create([
            'name' => 'Mecca Tours Ltd',
            'email' => 'mecca.partner@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_PARTNER,
            'status' => User::STATUS_ACTIVE,
            'company_name' => 'Mecca Tours & Travel',
            'company_registration_number' => 'TR789012',
            'contact_phone' => '+9876543210',
            'email_verified_at' => now(),
        ]);
        
        // Create suspended user
        $suspendedUser = User::create([
            'name' => 'Suspended User',
            'email' => 'suspended@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_SUSPENDED,
            'suspend_reason' => 'Violation of terms of service',
            'email_verified_at' => now(),
        ]);
        
        // Create hotel provider
        $hotelProvider = User::create([
            'name' => 'Al-Haram Hotel Group',
            'email' => 'hotel.provider@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_HOTEL_PROVIDER,
            'status' => User::STATUS_ACTIVE,
            'company_name' => 'Al-Haram Hotel Group',
            'company_registration_number' => 'HT456789',
            'contact_phone' => '+5555555555',
            'email_verified_at' => now(),
        ]);
        
        // Create transport provider
        $transportProvider = User::create([
            'name' => 'Medina Transport Co.',
            'email' => 'transport.provider@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TRANSPORT_PROVIDER,
            'status' => User::STATUS_PENDING,
            'company_name' => 'Medina Transport Services',
            'company_registration_number' => 'TP987654',
            'contact_phone' => '+7777777777',
            'email_verified_at' => now(),
        ]);
        
        // Create user verifier admin
        $userVerifier = User::create([
            'name' => 'User Verifier Admin',
            'email' => 'verifier@seferet.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $userVerifier->assignRole('user_verifier');
        
        // Create package verifier admin
        $packageVerifier = User::create([
            'name' => 'Package Verifier Admin',
            'email' => 'package.verifier@seferet.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $packageVerifier->assignRole('package_verifier');
        
        $this->command->info('Test users created successfully!');
        $this->command->info('Credentials: all users have password "password"');
    }
}
