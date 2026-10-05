<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\House;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a house.
 *
 * The server is the authority; the Zod schema on the client mirrors these
 * rules for immediate feedback only.
 */
class StoreHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $house = $this->route('house');

        // Updates go through the policy too, not just creation.
        return $house instanceof House
            ? $this->user()?->can('update', $house) === true
            : $this->user()?->can('create', House::class) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $houseId = $this->route('house')?->id;

        return [
            // Scoped by the global scope, so an id from another owner simply
            // will not validate.
            'property_id' => ['required', 'integer', Rule::exists('properties', 'id')],
            'unit' => [
                'required', 'string', 'max:50',
                // Unit labels are unique per property.
                Rule::unique('houses', 'unit')
                    ->where(fn ($q) => $q->where('property_id', $this->input('property_id')))
                    ->ignore($houseId),
            ],
            'type' => ['required', 'string', Rule::in(['flat', 'bedsitter', 'studio', 'apartment', 'house'])],
            'rent' => ['required', 'string', 'numeric', 'min:0', 'max:99999999.99'],
            'water_meter' => ['nullable', 'string', 'max:50'],
            'elec_meter' => ['nullable', 'string', 'max:50'],
            // Status is derived from tenancy, not set by hand.
            'status' => ['sometimes', Rule::in(['occupied', 'vacant'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit.unique' => 'That unit number already exists in this property.',
            'rent.numeric' => 'Rent must be a valid amount, for example 6500.00.',
        ];
    }
}