<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MaintenancePriority;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for raising a maintenance request or broadcasting a notice.
 *
 * A renter is bound to their own tenant_id and their own house: neither is
 * taken from the request body. Cost and vendor fields are staff-only, so a
 * renter cannot post themselves a zero-cost "completed" repair.
 */
class StoreMaintenanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('record');

        return $record instanceof MaintenanceRecord
            ? $this->user()?->can('update', $record) === true
            : $this->user()?->can('create', MaintenanceRecord::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isRenter = $this->user() instanceof Renter;
        $isOwner = $this->user() instanceof Owner;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', Rule::in(MaintenancePriority::values())],

                        /*
             * A renter never sends tenant_id: recordAttributes() overwrites it
             * with the authenticated actor. `prohibited` rather than
             * `nullable` so a crafted value is rejected outright instead of
             * passing validation and then being silently discarded.
             */
            'tenant_id' => [$isRenter ? 'prohibited' : 'required', 'integer', Rule::exists('tenants', 'id')],
            'house_id' => [$isRenter ? 'nullable' : 'nullable', 'integer', Rule::exists('houses', 'id')],
            'property_id' => ['nullable', 'integer', Rule::exists('properties', 'id')],

            // Staff-only workflow fields.
            'status' => [$isRenter ? 'prohibited' : 'nullable', Rule::in(['pending', 'in-progress', 'completed', 'cancelled'])],
            'assigned_to' => [$isRenter ? 'prohibited' : 'nullable', 'string', 'max:255'],
            'cost' => [$isRenter ? 'prohibited' : 'nullable', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'cost_notes' => [$isRenter ? 'prohibited' : 'nullable', 'string', 'max:5000'],
            'vendor_name' => [$isRenter ? 'prohibited' : 'nullable', 'string', 'max:255'],
            'vendor_phone' => [$isRenter ? 'prohibited' : 'nullable', 'string', 'max:50'],
            'scheduled_date' => [$isRenter ? 'prohibited' : 'nullable', 'date'],
            'completed_date' => [$isRenter ? 'prohibited' : 'nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],

            // Broadcasting is an owner action.
            'recipient_type' => [$isOwner ? 'nullable' : 'prohibited', Rule::in(['individual', 'property', 'all'])],
            'recipient_ids' => [$isOwner ? 'nullable' : 'prohibited', 'array'],
            'recipient_ids.*' => ['integer', Rule::exists('tenants', 'id')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function recordAttributes(): array
    {
        $data = $this->validated();
        $user = $this->user();

        if ($user instanceof Renter) {
            // A renter's request is always against their own tenancy.
            $data['tenant_id'] = (int) $user->getKey();
            $data['house_id'] = $user->house_id;
            $data['property_id'] = $user->property_id;
            unset($data['status'], $data['assigned_to'], $data['cost'], $data['cost_notes'],
                $data['vendor_name'], $data['vendor_phone'], $data['scheduled_date'],
                $data['completed_date']);
        }

        $data['status'] ??= 'pending';

        if (isset($data['recipient_ids']) && is_array($data['recipient_ids'])) {
            $data['recipient_ids'] = json_encode(array_values(array_map('intval', $data['recipient_ids'])));
        }

        return $data;
    }
}