<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

/** Locked out of 2FA? Run on the server: php artisan admin:2fa-reset you@example.com */
class AdminTwoFactorReset extends Command
{
    protected $signature = 'admin:2fa-reset {email : The admin\'s email}';

    protected $description = 'Clear an admin\'s two-factor secret and recovery codes so they re-enrol at next login';

    public function handle(): int
    {
        $admin = Admin::where('email', $this->argument('email'))->first();

        if ($admin === null) {
            $this->components->error('No admin with that email.');

            return self::FAILURE;
        }

        $admin->forceFill(['two_factor_secret' => null, 'two_factor_recovery' => null])->save();

        $this->components->info("2FA cleared for {$admin->email}. They will be asked to enrol on next login.");

        return self::SUCCESS;
    }
}
