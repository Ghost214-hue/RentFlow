<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A maintenance request.
 *
 * Maps the EXISTING `maintenance_records` table. A renter raises one against
 * their own unit; staff move it through pending -> in-progress -> completed.
 *
 * @property int $id
 * @property string $title
 * @property string|null $assigned_to
 * @property string $cost    Decimal string, never a float
 */
class MaintenanceRecord extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceRecordFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'maintenance_records';

    /** @return array<int, string> */
    protected $fillable = [
        'property_id',
        'house_id',
        'tenant_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'cost',
        'cost_notes',
        'vendor_name',
        'vendor_phone',
        'scheduled_date',
        'completed_date',
        'notes',
        'recipient_type',
        'recipient_ids',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            // Decimal stays a string: money is never a float.
            'cost' => 'decimal:2',
            'scheduled_date' => 'date',
            'completed_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
     * Whether the renter can no longer act on this record.
     */
    public function isClosed(): bool
    {
        $status = $this->status instanceof MaintenanceStatus
            ? $this->status
            : MaintenanceStatus::tryFrom((string) $this->status);

        return $status === MaintenanceStatus::Completed || $status === MaintenanceStatus::Cancelled;
    }

    /** @return array<int, int> */
    public function recipientIds(): array
    {
        if ($this->recipient_ids === null || trim($this->recipient_ids) === '') {
            return [];
        }

        $decoded = json_decode($this->recipient_ids, true);

        return is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
    }
}