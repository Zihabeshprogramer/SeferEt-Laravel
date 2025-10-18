<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        /* User::create([
            'name' => 'Seid Elias',
            'email' => 'admin@seferet.com',
            'password' => Hash::make('Seyako@0011'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'phone' => '+251-911-285865',
            'notes' => 'Default system administrator account created via seeder.',
        ]); */

        /* $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@seferet.com');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the default password after first login!'); */
    }
}
