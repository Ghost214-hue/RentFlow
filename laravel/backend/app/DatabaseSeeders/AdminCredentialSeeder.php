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

        // NOTE: do NOT pre-fill two_factor_secret / two_factor_recovery here.
        // A random hex string is not a valid base32 TOTP secret and nobody holds
        // a matching authenticator, so the admin would be locked out at 2FA.
        // Leave both empty: first login forces proper enrolment (QR + recovery codes).

        $this->command->info('Super admin credentials created successfully.');
        $this->command->info('Email: ' . $adminEmail);
        $this->command->info('Password: ' . $adminPassword);
        $this->command->info('Please change the password after first login.');
        $this->command->info('Two-factor authentication is mandatory for admin access.');
    }
}