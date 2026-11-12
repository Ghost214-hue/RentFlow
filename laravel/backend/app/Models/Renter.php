<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Enums\RenterStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A renter.
 *
 * NAMING: the database table is `tenants`, which in RentFlow means RENTERS,
 * not SaaS tenants. The model is therefore called `Renter` to avoid
 * colliding with the owner-tenancy axis; `$table` points at `tenants`.
 *
 * Maps the EXISTING `tenants` table — no schema changes.
 *
 * Extends Authenticatable rather than App\Models\Model because a renter signs
 * in: SessionGuard::login() type-hints Authenticatable, so without this every
 * renter login fatals with a TypeError. The money columns this model inherited
 * from App\Models\Model are cast explicitly in casts() below.
 *
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $deposit   Static snapshot; charged on the FIRST bill only
 * @property string $balance   Cached total outstanding (drifts — see defect 5)
 * @property string $credit    Cached overpayment (drifts — see defect 5)
 * @property string $status
 */
class Renter extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\RenterFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'tenants';

    /**
     * NOTE: there is NO `rent` column on `tenants` in the live schema, even
     * though the legacy TenantController referenced one and docs describe it
     * as an onboarding snapshot. Only `houses.rent` exists and is the source
     * of truth for bill generation. Attempting to write `rent` here throws
     * "Unknown column 'rent'", so it is deliberately absent from $fillable.
     *
     * @return array<int, string>
     */
    protected $fillable = [
        'property_id',
        'house_id',
        'name',
        'email',
        'profile_picture',
        'password',
        'phone',
        'id_number',
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_email',
        'id_type',
        'lease_start',
        'lease_end',
        'deposit',
        'balance',
        'credit',
        'water_balance',
        'elec_balance',
        'status',
        'documents',
        'data_protection_consent_at',
    ];

    /**
     * Money and dates. Decimal columns stay strings — never floats.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'deposit' => 'decimal:2',
            'balance' => 'decimal:2',
            'credit' => 'decimal:2',
            'water_balance' => 'decimal:2',
            'elec_balance' => 'decimal:2',
            'status' => RenterStatus::class,
            'lease_start' => 'date',
            'lease_end' => 'date',
            'data_protection_consent_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function role(): Role
    {
        return Role::Tenant;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'tenant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    /** Complaints raised by this renter. */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'tenant_id');
    }

    /** Maintenance requests raised by this renter. */
    public function maintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'tenant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}