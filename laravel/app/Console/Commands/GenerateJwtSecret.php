<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generates a shared JWT secret for the legacy sign-in bridge.
 *
 * WHY THIS EXISTS
 * JWT_SECRET shipped as an EMPTY value, which is the one setting that makes the
 * bridge worse than useless: hash_hmac accepts an empty key, so anybody could
 * forge a token for any owner. An empty value is easy to miss because the key
 * IS present in .env -- it just has no characters.
 *
 * Run this, then copy the value into BOTH this app's .env and the legacy app's
 * config, or the two will disagree and nobody will be able to sign in.
 *
 *     php artisan jwt:secret
 */
class GenerateJwtSecret extends Command
{
    protected $signature = 'jwt:secret {--show : Print the value without editing .env}';

    protected $description = 'Generate a secure shared JWT secret for the legacy sign-in bridge';

    /*
     * random_bytes() is PHP's CSPRNG, and unpredictability is the property that
     * actually matters here. 48 bytes is 384 bits -- far beyond brute force.
     *
     * Deliberately NOT the framework encrypter: its randomBytes() does not
     * exist in this Laravel version, and pulling in a dependency to generate a
     * random number would be the wrong trade anyway.
     */
    public function handle(): int
    {
        $base64 = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');

        $line = 'JWT_SECRET='.$base64;

        $this->newLine();
        $this->components->twoColumnDetail('Generated JWT_SECRET', $line);

        if ($this->option('show')) {
            return self::SUCCESS;
        }

        $envPath = base_path('.env');

        if (! is_file($envPath) || ! is_writable($envPath)) {
            $this->components->warn('.env is not writable. Copy the value above in by hand.');

            return self::SUCCESS;
        }

        $contents = (string) file_get_contents($envPath);
        $updated = preg_match('/^JWT_SECRET=.*$/m', $contents, $m)
            ? preg_replace('/^JWT_SECRET=.*$/m', $line, $contents, 1)
            : rtrim($contents, "\n")."\n".$line."\n";

        file_put_contents($envPath, $updated);

        $this->components->info('Written to .env. Copy the SAME value into the legacy app.');
        $this->components->warn(
            'Run `php artisan config:clear` afterwards, and set the legacy app to use HTTPS cookies.'
        );

        return self::SUCCESS;
    }
}
