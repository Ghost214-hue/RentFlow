<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Owner;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a property.
 *
 * `units` and `occupied` are DERIVED counts: the authoritative number of units
 * is the number of house rows, and occupancy is how many are occupied. They are
 * accepted so the legacy form keeps working, but HouseController/PropertyService
 * keeps them in step; the list view always recomputes from houses.
 */
class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            ? $this->user()?->can('update', $property) === true
            : $this->user()?->can('create', Property::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $propertyId = $this->route('property')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            // NOT NULL in the live schema, so a value is required even though
            // the column is not otherwise constrained.
            'address' => ['required', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'string', 'max:500'],

            // Money as a decimal string; never a float.
            'rent' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999.99'],

            'units' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'occupied' => ['nullable', 'integer', 'min:0', 'max:100000'],

            'payment_method_type' => [
                'nullable',
                Rule::in(['paybill', 'till', 'bank', 'mobile_money']),
            ],

            // Payment details are only meaningful for the chosen method, but
            // each is stored in its own column so they are validated
            // individually rather than conditionally discarded.
            'paybill_number' => ['nullable', 'string', 'max:50'],
            'paybill_account' => ['nullable', 'string', 'max:100'],
            'till_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'mobile_money_number' => ['nullable', 'string', 'max:50'],

            'caretaker_id' => [
                'nullable',
                'integer',
                // Must be a caretaker that exists. Scoped to the owner by
                // tenancy, so a foreign id cannot be attached.
                Rule::exists('caretakers', 'id'),
            ],
        ];
    }
}