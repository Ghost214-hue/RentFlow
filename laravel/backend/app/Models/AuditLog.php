<?php

declare(stypes=1);

namespace App\Models;

/**
 * Audit log entry for tracking all major application actions.
 *
 * @property int $id
 * @property string $actor_type        'admin' | 'owner' | 'caretaker' | 'renter'
 * @property int $actor_id
 * @property int $target_owner_id
 * @property string $action            'create' | 'update' | 'delete' | 'view' | 'login' | 'logout' | 'profile_switch'
 * @property string $subject_type      The type of entity affected
 * @property int $subject_id
 * @property string $reason            Human-readable explanation
 * @property string $ip_address        Source IP address
 * @property string $user_agent       Browser/user agent string
 * @property \Carbon\Carbon $created_at
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_type',
        'actor_id',
        'target_owner_id',
        'action',
        'subject_type',
        'subject_id',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function actor(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('actor');
    }

    public function targetOwner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Owner::class, 'target_owner_id');
    }
}