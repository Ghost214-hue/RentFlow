<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validation for creating an owner account.
 *
 * TWO DELIBERATE DIFFERENCES FROM THE LEGACY ENDPOINT
 *
 * 1. Minimum length is 8, not 6. Six characters is brute-forceable against a
 *    leaked hash; 8 is the floor everywhere else in this app.
 *
 * 2. The email uniqueness rule spans ALL THREE account tables, not just
 *    owners. The legacy check only looked at `owners`, so an address that was
 *    already a tenant's could be registered as a new owner -- which matters
 *    here, because password reset resolves an account by address and would
 *    then find the wrong one.
 *
 * Registration is deliberately open: this is a self-serve SaaS signup, and
 * tenancy isolation means a new owner starts with an empty portfolio. Abuse is
 * handled by rate limiting, not by hiding the endpoint.
 */
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::notIn($this->emailsAlreadyInUse()),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * Every address already registered to anyone, across all three tables.
     *
     * Returned as a list so Rule::notIn can reject them in one rule; the
     * controller re-checks inside a transaction regardless, because two
     * simultaneous signups would both pass validation and then race.
     *
     * @return list<string>
     */
    private function emailsAlreadyInUse(): array
    {
        $email = strtolower(trim((string) $this->input('email')));

        if ($email === '') {
            return [];
        }

        return array_values(array_unique(array_filter([
            Owner::query()->where('email', $email)->value('email'),
            Renter::query()->withoutGlobalScopes()->where('email', $email)->value('email'),
            Caretaker::query()->withoutGlobalScopes()->where('email', $email)->value('email'),
        ])));
    }

    public function messages(): array
    {
        return [
            'email.not_in' => 'An account already exists for this email address.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }

        if ($this->has('name')) {
            $this->merge(['name' => trim((string) $this->input('name'))]);
        }
    }
}
