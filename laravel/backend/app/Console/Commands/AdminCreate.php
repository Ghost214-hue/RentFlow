<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/** php artisan admin:create you@example.com --name="Your Name" --role=super_admin */
class AdminCreate extends Command
{
    protected $signature = 'admin:create {email} {--name=Admin} {--role=super_admin : super_admin|support|auditor}';

    protected $description = 'Create a platform admin (password prompted, never stored in source)';

    public function handle(): int
    {
        $role = (string) $this->option('role');

        if (! in_array($role, ['super_admin', 'support', 'auditor'], true)) {
            $this->components->error('Role must be super_admin, support or auditor.');

            return self::FAILURE;
        }

        if (Admin::where('email', $this->argument('email'))->exists()) {
            $this->components->error('An admin with that email already exists.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (min 12 characters)');

        if (strlen($password) < 12) {
            $this->components->error('Password too short.');

            return self::FAILURE;
        }

        Admin::create([
            'name' => (string) $this->option('name'),
            'email' => (string) $this->argument('email'),
            'password' => Hash::make($password),
            'status' => 'active',
            'admin_role' => $role,
            'two_factor_required' => true,
        ]);

        $this->components->info('Admin created. They enrol 2FA at first login.');

        return self::SUCCESS;
    }
}
