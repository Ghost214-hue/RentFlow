<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Step 1 of a password reset: ask for the address, email a code.
 *
 * THE RESPONSE IS IDENTICAL WHETHER OR NOT THE ADDRESS EXISTS.
 *
 * The legacy endpoint answered 404 "No account found with that email address",
 * which is an account-enumeration oracle: anybody could harvest which addresses
 * have RentFlow accounts and then aim a phishing campaign at exactly those
 * people. This controller always renders the same page with the same wording,
 * so the response tells an attacker nothing.
 */
class PasswordResetLinkController extends Controller
{
    public function __construct(private readonly PasswordResetService $resets) {}

    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'flash' => ['success' => session('status')],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Throttled by the `password-reset` named limiter on the route (per
        // email+IP), so neither one address nor one attacker cycling addresses
        // can flood a real mailbox.

        $this->resets->request((string) $data['email']);

        return back()->with('status', __(
            'If that address belongs to a RentFlow account, we have sent it a verification code. Check your inbox (and spam folder).'
        ));
    }
}
