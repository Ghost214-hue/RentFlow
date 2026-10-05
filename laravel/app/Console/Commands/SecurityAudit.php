<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Reports configuration that is unsafe in production.
 *
 * Read-only: it changes nothing. Run it before every deploy, and wire it into
 * CI so a misconfigured release fails the build rather than shipping.
 *
 *     php artisan security:audit
 *
 * Exits non-zero when anything CRITICAL is wrong, so it is usable as a gate.
 */
class SecurityAudit extends Command
{
    protected $signature = 'security:audit';

    protected $description = 'Check the configuration for settings that are unsafe in production';

    public function handle(): int
    {
        $critical = 0;
        $warnings = 0;

        $criticalCheck = function (string $label, bool $ok, string $fix) use (&$critical): void {
            if ($ok) {
                $this->components->twoColumnDetail($label, '<fg=green>ok</>');

                return;
            }

            $this->components->twoColumnDetail($label, '<fg=red>PROBLEM</>');
            $this->components->warn('  '.$fix);
            $critical++;
        };

        $warnCheck = function (string $label, bool $ok, string $fix) use (&$warnings): void {
            if ($ok) {
                $this->components->twoColumnDetail($label, '<fg=green>ok</>');

                return;
            }

            $this->components->twoColumnDetail($label, '<fg=yellow>WARN</>');
            $this->components->warn('  '.$fix);
            $warnings++;
        };

        $this->components->info('Critical (these are exploitable or leak data):');

        $criticalCheck(
            'APP_DEBUG off',
            ! (bool) config('app.debug'),
            'APP_DEBUG=true renders stack traces, file paths and env values to anyone. Set APP_DEBUG=false.'
        );

        $criticalCheck(
            'APP_KEY set',
            (string) config('app.key') !== '',
            'Run `php artisan key:generate`.'
        );

        $criticalCheck(
            'APP_ENV not local',
            config('app.env') !== 'local',
            'APP_ENV=local disables error reporting. Set APP_ENV=production.'
        );

        $criticalCheck(
            'session cookie secure',
            (bool) config('session.secure'),
            'SESSION_SECURE_COOKIE=true, so the session cookie is never sent over plain HTTP.'
        );

        $criticalCheck(
            'log level not debug',
            config('logging.default') !== 'debug',
            'Debug logging records request bodies, which include passwords and reset codes.'
        );

        $this->newLine();
        $this->components->info('Recommended:');

        $warnCheck(
            'session encrypted at rest',
            (bool) config('session.encrypt'),
            'SESSION_ENCRYPT=true. Session files on disk are otherwise readable by anything on the host.'
        );

        $warnCheck(
            'CSRF protection on',
            ! (config('session.driver') === 'array'),
            'CSRF middleware must stay in the web group.'
        );

        $warnCheck(
            'queue not synchronous',
            config('queue.default') !== 'sync',
            'With QUEUE_CONNECTION=sync, mail and any background work run inside the request. Start `php artisan queue:work`.'
        );

        $warnCheck(
            'mailer not log',
            config('mail.default') !== 'log',
            'MAIL_MAILER=log writes mail to the log file instead of sending it. Nobody is receiving anything.'
        );

        $warnCheck(
            '.env not world readable',
            ! File::exists(base_path('.env')) || (fileperms(base_path('.env')) & 0o044) === 0,
            'chmod 600 .env -- it holds the app key and database credentials.'
        );

        $warnCheck(
            'config not cached in production',
            config('app.env') !== 'production' || File::exists(base_path('bootstrap/cache/config.php')),
            'Run `php artisan config:cache` so env changes cannot alter a running app.'
        );

        $this->newLine();

        if ($critical > 0) {
            $this->components->error("{$critical} critical problem(s).");

            return self::FAILURE;
        }

        $this->components->info($warnings > 0
            ? "No critical problems. {$warnings} recommendation(s) outstanding."
            : 'No problems found.');

        return self::SUCCESS;
    }
}
