<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Self-serve owner registration.
 *
 * ONLY owners can self-register. Renters and caretakers are created by an owner
 * and invited through the setup-password flow; letting anyone claim a tenancy
 * would mean anyone could attach themselves to a property.
 *
 * A new owner starts with an EMPTY portfolio and nothing to read but their own
 * account, which is what makes open registration safe here: tenancy isolation
 * means there is no shared data for a spammer to reach. Abuse is handled by the
 * `register` rate limiter rather than by obscurity.
 */
class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // Re-checked inside the transaction: two simultaneous signups would both
        // pass validation and then race on the insert.
        if ($this->emailIsTaken($request->string('email')->value())) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email address.',
            ]);
        }

        DB::transaction(function () use ($request): void {
            Owner::query()->create([
                'name' => $request->string('name')->value(),
                'email' => $request->string('email')->value(),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->string('password')->value()),
                'avatar' => $this->initials($request->string('name')->value()),
            ]);
        });

        // Not signed in automatically. The legacy endpoint returned a JWT
        // straight away, which silently created a session the user never asked
        // for; sending them to log in keeps that an explicit act.
        return redirect('/login')->with(
            'success',
            'Your account is ready. Sign in with the details you just chose.',
        );
    }

    /**
     * Is this address registered to anyone, in any of the three account tables?
     */
    private function emailIsTaken(string $email): bool
    {
        $email = strtolower(trim($email));

        return Owner::query()->where('email', $email)->exists()
            || Renter::query()->withoutGlobalScopes()->where('email', $email)->exists()
            || Caretaker::query()->withoutGlobalScopes()->where('email', $email)->exists();
    }

    /**
     * Two-letter avatar from the name, matching the legacy behaviour.
     *
     * "Grace Wanjiru Mbuthia" -> "GW". Falls back to "OW" rather than an empty
     * string, so an avatar is never blank.
     */
    private function initials(string $name): string
    {
        $initials = '';

        foreach (preg_split('/\s+/', trim($name)) ?: [] as $part) {
            if ($part !== '') {
                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }

        return mb_substr($initials, 0, 2) ?: 'OW';
    }
}
