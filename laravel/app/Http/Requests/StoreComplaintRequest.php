<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ComplaintPriority;
use App\Models\Complaint;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for raising a complaint or broadcasting a notice.
 *
 * A renter may only file against themselves; tenant_id is taken from the
 * authenticated actor rather than the request body.
 */
class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        $complaint = $this->route('complaint');

        return $complaint instanceof Complaint
            ? $this->user()?->can('update', $complaint) === true
            : $this->user()?->can('create', Complaint::class) === true;
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
            'priority' => ['nullable', Rule::in(ComplaintPriority::values())],

            // A renter cannot name somebody else as the subject.
                        /*
             * A renter never sends tenant_id: complaintAttributes() overwrites
             * it with the authenticated actor. `prohibited` rather than
             * `nullable` so a crafted value is rejected outright instead of
             * passing validation and then being silently discarded.
             */
            'tenant_id' => [$isRenter ? 'prohibited' : 'required', 'integer', Rule::exists('tenants', 'id')],
            'house_id' => ['nullable', 'integer', Rule::exists('houses', 'id')],
            'property_id' => ['nullable', 'integer', Rule::exists('properties', 'id')],

            // Broadcasting to several renters is an owner/caretaker action.
            'recipient_type' => [$isOwner ? 'nullable' : 'prohibited', Rule::in(['individual', 'property', 'all'])],
            'recipient_ids' => [$isOwner ? 'nullable' : 'prohibited', 'array'],
            'recipient_ids.*' => ['integer', Rule::exists('tenants', 'id')],
        ];
    }

    /**
     * Normalised payload. sender_role is derived from the actor, never sent
     * by the client.
     *
     * @return array<string, mixed>
     */
    public function complaintAttributes(): array
    {
        $data = $this->validated();
        $user = $this->user();

        $data['sender_role'] = match (true) {
            $user instanceof Owner => 'owner',
            $user instanceof Renter => 'tenant',
            default => 'caretaker',
        };

        if ($user instanceof Renter) {
            $data['tenant_id'] = (int) $user->getKey();
        }

        $data['status'] = 'open';
        $data['date'] = $data['date'] ?? now()->toDateString();

        // A broadcast stores its audience as JSON; a direct complaint keeps
        // the single tenant_id.
        if (isset($data['recipient_ids']) && is_array($data['recipient_ids'])) {
            $data['recipient_ids'] = json_encode(array_values(array_map('intval', $data['recipient_ids'])));
        }

        $data['read_by'] = null;

        return $data;
    }
}