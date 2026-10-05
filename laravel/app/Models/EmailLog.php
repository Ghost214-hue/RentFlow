<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailLogStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A record of one outbound email and whether it was delivered.
 *
 * Maps the EXISTING `email_logs` table. This is an append-only delivery log:
 * it records what was sent, never what should be sent, and is never edited.
 *
 * @property int $id
 * @property string $to_email
 * @property string $subject
 * @property string $status   sent|failed|pending
 * @property string|null $error
 */
class EmailLog extends Model
{
    /** @use HasFactory<\Database\Factories\EmailLogFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'email_logs';

    /**
     * There is NO `updated_at` column on email_logs -- only `created_at` and
     * `sent_at`. Without this, every insert tries to write `updated_at` and
     * fails with "Unknown column 'updated_at' in 'field list'".
     *
     * The log is also append-only by design: a delivery record is never edited,
     * so there is nothing for updated_at to track.
     */
    public $timestamps = false;

    protected $fillable = [
        'to_email',
        'to_name',
        'subject',
        'body',
        'status',
        'error',
        'sent_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => EmailLogStatus::class,
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function scopeFailed($query)
    {
        return $query->where('status', EmailLogStatus::Failed);
    }

    public function scopePending($query)
    {
        return $query->where('status', EmailLogStatus::Pending);
    }
}