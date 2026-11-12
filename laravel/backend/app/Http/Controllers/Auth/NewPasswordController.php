<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Step 2 of a password reset: verify the code and set a new password.
 *
 * A wrong code, an expired code and an unknown address all produce the SAME
 * error, for the same reason as step 1: the difference would tell an attacker
 * whether a guess was close.
 */
class NewPasswordController extends Controller
{
    public function __construct(private readonly PasswordResetService $resets) {}

    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => (string) $request->query('email', ''),
            'flash' => ['success' => session('status')],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $ok = $this->resets->reset(
            (string) $data['email'],
            (string) $data['code'],
            (string) $data['password'],
        );

        if (! $ok) {
            throw ValidationException::withMessages([
                // Keyed to `code`, and worded to cover every failure mode.
                'code' => 'That code is not valid or has expired. Request a new one and try again.',
            ]);
        }

        event(new PasswordReset($request->user()));

        return redirect('/login')->with(
            'success',
            'Your password has been changed. You can sign in with it now.',
        );
    }
}
