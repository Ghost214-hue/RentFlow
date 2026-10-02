<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Caretaker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation for creating/updating a caretaker. */
class StoreCaretakerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $caretaker = $this->route('caretaker');

        return $caretaker instanceof Caretaker
            ? $this->user()?->can('update', $caretaker) === true
            : $this->user()?->can('create', Caretaker::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $caretakerId = $this->route('caretaker')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                // One caretaker account per owner per email.
                Rule::unique('caretakers', 'email')->ignore($caretakerId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:100'],

            // Optional on create: the account is activated later by the
            // owner sending a setup link.
            'password' => [$caretakerId === null ? 'nullable' : 'sometimes', 'string', 'min:8', 'max:255'],

            'assigned_properties' => ['nullable', 'array'],
            'assigned_properties.*' => ['integer', Rule::exists('properties', 'id')],
        ];
    }

    /**
     * assigned_properties is a CSV text column; store it in that shape.
     *
     * @return array<string, mixed>
     */
    public function caretakerAttributes(): array
    {
        $data = $this->validated();

        if (array_key_exists('assigned_properties', $data)) {
            $ids = array_values(array_map('intval', (array) $data['assigned_properties']));
            $data['assigned_properties'] = $ids === [] ? null : implode(',', $ids);
        }

        return $data;
    }
}