<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A complaint in the two-way communication flow.
 *
 * Maps the EXISTING `complaints` table. sender_role records who raised it
 * (tenant | owner | caretaker), and recipient_ids holds a JSON array of renter
 * ids when the complaint was broadcast to a property or everyone.
 *
 * @property int $id
 * @property int $owner_id
 * @property string $title
 * @property string $status
 * @property string $priority
 * @property string|null $sender_role
 * @property string|null $recipient_ids  JSON array of renter ids
 * @property string|null $read_by       JSON array of user ids
 */
class Complaint extends Model
{
    /** @use HasFactory<\Database\Factories\ComplaintFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'complaints';

    /** @return array<int, string> */
    protected $fillable = [
        'tenant_id',
        'house_id',
        'title',
        'category',
        'priority',
        'status',
        'date',
        'description',
        'timeline',
        'comments',
        'sender_role',
        'recipient_type',
        'recipient_ids',
        'property_id',
        'read_by',
        'read_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'priority' => ComplaintPriority::class,
            'status' => ComplaintStatus::class,
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Renter::class, 'tenant_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Renter ids this complaint was addressed to.
     *
     * recipient_ids is a JSON array in a TEXT column; a LIKE match against the
     * raw column (as the legacy code did) can match a partial id, so it is
     * decoded and compared as integers.
     *
     * @return array<int, int>
     */
    public function recipientIds(): array
    {
        if ($this->recipient_ids === null || trim($this->recipient_ids) === '') {
            return [];
        }

        $decoded = json_decode($this->recipient_ids, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }

    /**
     * Has this user already read the complaint?
     */
    public function isReadBy(int $userId): bool
    {
        if ($this->read_by === null || trim($this->read_by) === '') {
            return false;
        }

        $decoded = json_decode($this->read_by, true);

        return is_array($decoded) && in_array($userId, array_map('intval', $decoded), true);
    }

    /** @return array<int, int> */
    public function readByIds(): array
    {
        $decoded = $this->read_by === null ? [] : json_decode($this->read_by, true);

        return is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
    }

    /**
     * Timeline entries, stored as a JSON array in a TEXT column.
     *
     * @return array<int, array<string, mixed>>
     */
    public function timelineEntries(): array
    {
        $decoded = $this->timeline === null ? [] : json_decode($this->timeline, true);

        return is_array($decoded) ? $decoded : [];
    }
}