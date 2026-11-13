<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Audit for duplicate emails across actor tables.
 *
 * Reports emails that appear in more than one actor table, or twice within
 * the same table. Does NOT modify any data.
 */
class IdentityAudit extends Command
{
    protected $signature = 'identity:audit';
    protected $description = 'Find duplicate emails across owner, caretaker, and renter tables';

    public function handle(): int
    {
        $tables = [
            'owners' => Owner::class,
            'caretakers' => Caretaker::class,
            'tenants' => Renter::class,
        ];

        $found = false;

        foreach ($tables as $table => $model) {
            $dupes = DB::table($table)
                ->select('email', DB::raw('COUNT(*) as cnt'))
                ->whereNotNull('email')
                ->groupBy('email')
                ->having('cnt', '>', 1)
                ->get();

            foreach ($dupes as $dupe) {
                $found = true;
                $this->warn("DUPLICATE in {$table}: email={$dupe->email} count={$dupe->cnt}");
            }
        }

        // Cross-table duplicates
        $allEmails = [];
        foreach ($tables as $table => $model) {
            $rows = DB::table($table)->whereNotNull('email')->pluck('email');
            foreach ($rows as $email) {
                $allEmails[$email][] = $table;
            }
        }

        foreach ($allEmails as $email => $occurrences) {
            if (count($occurrences) > 1 || array_count_values($occurrences)[array_count_values($occurrences) ?? []] > 1 ?? false) {
                $uniqueTables = array_unique($occurrences);
                if (count($uniqueTables) > 1) {
                    $found = true;
                    $this->warn("CROSS-TABLE: email={$email} found in " . implode(', ', $uniqueTables));
                }
            }
        }

        if (! $found) {
            $this->info('No duplicate emails found across actor tables.');
        }

        return $found ? 1 : 0;
    }
}
