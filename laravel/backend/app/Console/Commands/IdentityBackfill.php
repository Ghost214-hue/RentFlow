<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Backfill users table and user_id FKs on actor tables.
 *
 * Does NOT auto-merge collisions: reports and skips them. Use --dry-run to
 * preview without writing.
 */
class IdentityBackfill extends Command
{
    protected $signature = 'identity:backfill {--dry-run : Preview changes without writing}';
    protected $description = 'Backfill users table and link actors by user_id';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN — no changes will be written.');
        }

        $actors = [
            'owners' => Owner::class,
            'caretakers' => Caretaker::class,
            'tenants' => Renter::class,
        ];

        foreach ($actors as $table => $modelClass) {
            $rows = DB::table($table)->whereNotNull('email')->get();

            foreach ($rows as $row) {
                // Find or create user
                $user = User::where('email', $row->email)->first();

                if ($user === null) {
                    $userData = [
                        'email' => $row->email,
                        'password' => $row->password ?? '',
                        'status' => 'active',
                    ];

                    if ($dryRun) {
                        $this->info("Would create user: {$row->email}");
                    } else {
                        $user = User::create($userData);
                    }
                }

                // Link actor to user
                if ($dryRun) {
                    $this->info("Would set user_id={$user->id} on {$table} id={$row->id}");
                } else {
                    DB::table($table)->where('id', $row->id)->update(['user_id' => $user->id]);
                }
            }
        }

        $this->info('Identity backfill complete.' . ($dryRun ? ' (dry run — no changes written)' : ''));

        return 0;
    }
}
