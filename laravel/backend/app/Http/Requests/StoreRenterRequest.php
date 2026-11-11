<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for creating/updating a renter.
 *
 * Ported from TenantController::store()/update(). Two legacy rules are kept:
 *   - a renter may only ever change phone, email and profile picture;
 *   - the unit must be VACANT, claimed atomically, or onboarding fails.
 */
class StoreRenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $renter = $this->route('renter');

        if ($renter instanceof Renter) {
            return $this->user()?->can('update', $renter) === true;
        }

        return $this->user()?->can('create', Renter::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $actor = $this->user();
        $isRenter = $actor instanceof Renter;
        $renterId = $this->route('renter')?->id;

        // A renter editing themselves may change contact details only.
        if ($isRenter) {
            return [
                'phone' => ['sometimes', 'string', 'max:30'],
                'email' => ['sometimes', 'nullable', 'email', 'max:255'],
                'profile_picture' => ['sometimes', 'nullable', 'string', 'max:255'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],

            'id_type' => ['required', Rule::in(['National ID', 'Passport', 'Birth Certificate'])],
            'id_number' => ['nullable', 'string', 'max:50'],

            'property_id' => ['required', 'integer', Rule::exists('properties', 'id')],
            'house_id' => ['required', 'integer', Rule::exists('houses', 'id')],

            // Rent and deposit are money: validated as decimal strings and
            // never converted to a float in the browser.
            'rent' => ['required', 'string', 'numeric', 'min:0', 'max:99999999.99'],
            'deposit' => ['nullable', 'string', 'numeric', 'min:0', 'max:99999999.99'],
            'opening_balance' => ['nullable', 'string', 'numeric', 'min:0', 'max:99999999.99'],

            'lease_start' => ['nullable', 'date'],
            'lease_end' => ['nullable', 'date', 'after_or_equal:lease_start'],

            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:30'],
            'next_of_kin_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * Legacy rule: onboarding fails if the unit is already occupied.
     * Checked here for a clear field error; the atomic guard in the action
     * is what actually prevents a double-assignment race.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->has('house_id') === false || $this->has('property_id') === false) {
                return;
            }

            $house = \App\Models\House::withoutOwnerScope()
                ->find($this->integer('house_id'));

            if ($house === null) {
                return;
            }

            // The unit must belong to the chosen property.
            if ((int) $house->property_id !== $this->integer('property_id')) {
                $validator->errors()->add(
                    'house_id',
                    'That unit does not belong to the selected property.',
                );

                return;
            }

            if ($house->isOccupied()) {
                $validator->errors()->add('house_id', 'That unit is already occupied.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lease_end.after_or_equal' => 'The lease end date must be on or after the start date.',
            'rent.numeric' => 'Rent must be a valid amount, for example 6500.00.',
        ];
    }

    /**
     * Data to persist. owner_id is deliberately absent: the BelongsToOwner
     * creating hook stamps it from the session, so a request cannot forge it.
     *
     * @return array<string, mixed>
     */
    public function renterAttributes(): array
    {
        $data = $this->validated();

        // `opening_balance` is the legacy name for the renter's starting
        // arrears; the column is `balance`.
        if (array_key_exists('opening_balance', $data)) {
            $data['balance'] = $data['opening_balance'];
            unset($data['opening_balance']);
        }

        return $data;
    }
}