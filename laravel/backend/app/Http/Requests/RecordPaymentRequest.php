<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Renter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for recording a payment.
 *
 * A renter may only record a payment for THEMSELVES: the renter_id is taken
 * from the authenticated actor, never from the request body.
 */
class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user instanceof Renter) {
            return true;
        }

        // Owners and caretakers must name a renter they are allowed to pay.
        $renterId = (int) $this->input('renter_id');

        return $renterId > 0
            && $user?->can('create', \App\Models\Payment::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isRenter = $this->user() instanceof Renter;

        return [
            // Ignored for renters: overridden below.
            'renter_id' => [$isRenter ? 'nullable' : 'required', 'integer', Rule::exists('tenants', 'id')],

            // Money as a string: never a float in the browser or the request.
            'amount' => ['required', 'string', 'numeric', 'min:0.01', 'max:99999999.99'],

            'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'type' => ['nullable', Rule::in(['Rent', 'Deposit', 'Water', 'Electricity', 'Mixed'])],
            'method' => ['nullable', 'string', 'max:60'],
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.numeric' => 'Enter a valid amount, for example 6500.00.',
            'amount.min' => 'Amount must be greater than zero.',
            'month.regex' => 'Month must be in YYYY-MM format.',
        ];
    }

    /**
     * Normalised payload for RecordPayment. For a renter, renter_id is forced
     * to their own id so a forged body value cannot redirect the payment.
     *
     * @return array<string, mixed>
     */
    public function paymentAttributes(): array
    {
        $data = $this->validated();

        if ($this->user() instanceof Renter) {
            $data['tenant_id'] = (int) $this->user()->getKey();
        } else {
            $data['tenant_id'] = (int) $data['renter_id'];
            unset($data['renter_id']);
        }

        return $data;
    }
}