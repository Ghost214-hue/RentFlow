<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the first super admin from the environment.
 *
 * Set in .env, run once, then REMOVE the password from .env:
 *   ADMIN_SEED_EMAIL=you@example.com
 *   ADMIN_SEED_PASSWORD=a-long-random-password
 *
 * No credentials live in source control. Prefer `php artisan admin:create`.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_SEED_EMAIL', '');
        $password = (string) env('ADMIN_SEED_PASSWORD', '');

        if ($email === '' || strlen($password) < 12) {
            $this->command?->warn('Skipped: set ADMIN_SEED_EMAIL and a 12+ character ADMIN_SEED_PASSWORD, or run `php artisan admin:create`.');

            return;
        }

        if (Admin::where('email', $email)->exists()) {
            $this->command?->info('Admin already exists. Skipping.');

            return;
        }

        Admin::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 'active',
            'admin_role' => 'super_admin',
            'two_factor_required' => true,
        ]);

        $this->command?->info("Super admin {$email} created. Remove ADMIN_SEED_PASSWORD from .env. 2FA enrolment happens at first login.");
    }
}
