<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Renter;
use App\Support\Amount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The renter's own profile.
 *
 * A renter may edit their contact details but NOT their tenancy: status, rent,
 * deposit, lease dates and unit assignment belong to the owner. Those fields
 * are simply absent from the validation rules rather than trusted-then-
 * discarded, so a crafted request cannot reach them.
 */
class RenterProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $renter = $this->currentRenter($request);

        return Inertia::render('Renter/Profile', [
            'renter' => $this->profileData($renter),
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $renter = $this->currentRenter($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('tenants', 'email')->ignore($renter->getKey()),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'id_type' => ['nullable', 'string', 'max:50'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:50'],
            'next_of_kin_email' => ['nullable', 'email', 'max:255'],

            // Changing a password requires proving you know the current one.
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        // Never let a renter set their own status or tenancy.
        unset($validated['current_password'], $validated['password_confirmation']);

        $renter->fill($validated)->save();

        return back()->with('success', 'Profile updated.');
    }

    /** @return array<string, mixed> */
    private function profileData(Renter $renter): array
    {
        return [
            'id' => (int) $renter->getKey(),
            'name' => (string) $renter->name,
            'email' => (string) $renter->email,
            'phone' => $renter->phone,
            'id_number' => $renter->id_number,
            'id_type' => $renter->id_type,
            'next_of_kin_name' => $renter->next_of_kin_name,
            'next_of_kin_phone' => $renter->next_of_kin_phone,
            'next_of_kin_email' => $renter->next_of_kin_email,
            'profile_picture' => $renter->profile_picture,

            // Read-only tenancy, shown for context but not editable here.
            'status' => $renter->status instanceof \BackedEnum ? $renter->status->value : (string) $renter->status,
            'property_name' => $renter->property?->name,
            'house_unit' => $renter->house?->unit,
            'lease_start' => $renter->lease_start?->toDateString(),
            'lease_end' => $renter->lease_end?->toDateString(),
            'deposit' => Amount::toString(Amount::of($renter->deposit)),
            'balance' => Amount::toString(Amount::of($renter->balance)),
            'credit' => Amount::toString(Amount::of($renter->credit)),
        ];
    }

    private function currentRenter(Request $request): Renter
    {
        $renter = $request->user();

        abort_unless($renter instanceof Renter, 403);

        return $renter;
    }
}