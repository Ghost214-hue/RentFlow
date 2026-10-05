<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Caretaker;
use App\Models\Renter;
use App\Services\AccountSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Step 2 of onboarding: the invited user chooses their first password.
 *
 * The link arrives by email, so this page is reached with no session and no
 * prior knowledge of the account. It therefore reveals nothing about who the
 * account belongs to: an invalid token says only that the link is no longer
 * valid, which is the same thing a used, expired and forged link must all say.
 */
class SetupPasswordController extends Controller
{
    public function __construct(private readonly AccountSetupService $setups) {}

    public function show(Request $request): Response
    {
        $plain = (string) $request->query('token', '');
        $token = $plain === '' ? null : $this->setups->findUsable($plain);

        return Inertia::render('Auth/SetPassword', [
            'token' => $plain,
            'valid' => $token !== null,
            // Safe to show: it is already in the URL they clicked.
            'name' => $token === null ? null : $this->displayName($token->user_type, $token->user_id),
            'flash' => ['success' => session('status')],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Throttled per token so a stolen or guessed link cannot be ground
        // down; the token is 256 bits so this is belt-and-braces.
        $key = 'setup:'.substr(hash('sha256', (string) $data['token']), 0, 32);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'token' => 'Too many attempts. Request a new invitation link.',
            ]);
        }

        RateLimiter::hit($key, 900);

        if (! $this->setups->complete((string) $data['token'], (string) $data['password'])) {
            throw ValidationException::withMessages([
                'token' => 'This link is no longer valid. It may have been used already, or expired.',
            ]);
        }

        return redirect('/login')->with(
            'success',
            'Your password is set. Sign in with the email your invitation was sent to.',
        );
    }

    /**
     * Ask for another invitation.
     *
     * The same response whether or not the account exists, so this cannot be
     * used to discover who has an account.
     */
    public function resend(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_type' => ['required', 'in:tenant,caretaker'],
            'user_id' => ['required', 'integer', 'min:1'],
        ]);

        RateLimiter::hit('setup-resend:'.$request->ip(), 3600);

        $this->setups->resend((string) $data['user_type'], (int) $data['user_id']);

        return back()->with(
            'status',
            'If that account exists, a new invitation is on its way.',
        );
    }

    private function displayName(string $userType, int $userId): ?string
    {
        $account = $userType === 'tenant'
            ? Renter::query()->withoutGlobalScopes()->find($userId)
            : Caretaker::query()->withoutGlobalScopes()->find($userId);

        return $account === null ? null : (string) $account->name;
    }
}
