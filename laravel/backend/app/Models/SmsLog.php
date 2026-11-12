<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SmsLogStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A record of one outbound SMS and whether it was delivered.
 *
 * Maps the `sms_logs` table. Append-only delivery log: records what was sent,
 * never what should be sent, never edited.
 *
 * @property int $id
 * @property string $to_phone
 * @property string $to_name
 * @property string $message
 * @property string $status   pending|sent|failed
 * @property string|null $error
 */
class SmsLog extends Model
{
    /** @use HasFactory<\Database\Factories\SmsLogFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'sms_logs';

    public $timestamps = false;

    protected $fillable = [
        'to_phone',
        'to_name',
        'message',
        'status',
        'error',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SmsLogStatus::class,
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function scopeFailed($query)
    {
        return $query->where('status', SmsLogStatus::Failed);
    }

    public function scopePending($query)
    {
        return $query->where('status', SmsLogStatus::Pending);
    }
}