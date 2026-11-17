<?php

declare(strict_types=1);

namespace App\DatabaseSeeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminCredentialSeeder extends Seeder
{
    public function run(): void
    {
        // Create the first super admin account
        // This is a one-time setup that should be run after migrations

        $adminEmail = 'admin@rentflow.test';
        $adminPassword = 'admin123456';

        // Check if admin already exists
        if (Admin::where('email', $adminEmail)->exists()) {
            $this->command->info('Admin already exists. Skipping credential setup.');
            return;
        }

        // Create the admin with hashed password
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'admin_role' => 'super_admin',
            'two_factor_required' => true,
            'status' => 'active',
        ]);

        // Generate TOTP secret and recovery codes
        $admin->two_factor_secret = bin2hex(random_bytes(20));
        $admin->two_factor_recovery = implode(',', array_map(
            fn() => bin2hex(random_bytes(8)),
            range(1, 8)
        ));
        $admin->save();

        $this->command->info('Super admin credentials created successfully.');
        $this->command->info('Email: ' . $adminEmail);
        $this->command->info('Password: ' . $adminPassword);
        $this->command->info('Please change the password after first login.');
        $this->command->info('Two-factor authentication is mandatory for admin access.');
    }
}