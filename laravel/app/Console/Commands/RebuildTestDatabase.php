<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rebuild the test database from the DEV database structure.
 *
 * The suite must never use RefreshDatabase or migrate:fresh: RentFlow maps an
 * existing production schema and has no migrations, so migrate:fresh would drop
 * every table and leave an empty database. This task copies the SCHEMA of the
 * dev database (structure only, never rows) into the test database so the tests
 * exercise the real DDL.
 *
 * Usage: php artisan rentflow:test-db
 */
class RebuildTestDatabase extends Command
{
    protected $signature = 'rentflow:test-db
        {--source=rentalflow : Source database to copy the structure from}
        {--target=rentalflow_test : Database to rebuild}';

    protected $description = 'Rebuild the test database structure from the dev database (no data)';

    public function handle(): int
    {
        $source = (string) $this->option('source');
        $target = (string) $this->option('target');

        if ($source === $target) {
            $this->error('Source and target must differ.');

            return self::FAILURE;
        }

        $root = DB::connection('mysql');

        /*
         * Confirm the source really has the domain tables BEFORE dropping the
         * target, so a misconfigured --source cannot destroy the test database.
         */
        $probe = $root->selectOne(
            'SELECT COUNT(*) AS found FROM information_schema.tables
             WHERE table_schema = ? AND table_name = ?',
            [$source, 'owners']
        );

        if ((int) ($probe->found ?? 0) === 0) {
            $this->error("Source database `{$source}` has no `owners` table; refusing to rebuild.");

            return self::FAILURE;
        }

        $this->warn("Dropping and recreating `{$target}`. Its contents are disposable.");

        $root->statement("DROP DATABASE IF EXISTS `{$target}`");
        $root->statement("CREATE DATABASE `{$target}` CHARACTER SET utf8mb4");

        $this->info("Recreated `{$target}`. Now import the structure, e.g.:");
        $this->line("  mysqldump --no-data --skip-add-drop-table {$source} | mysql {$target}");

        return self::SUCCESS;
    }
}