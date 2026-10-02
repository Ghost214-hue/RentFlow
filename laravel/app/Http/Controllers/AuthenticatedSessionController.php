<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\Authenticator;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Session authentication for all three actor types.
 *
 * Establishes a Laravel session and resolves the owner context that every
 * tenant-scoped query depends on.
 */
class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $auth = new Authenticator(
            mb_strtolower(trim((string) $request->string('email'))),
            (string) $request->string('password'),
        );

        $actor = $auth->attempt();

        if ($actor === null) {
            $request->recordLoginAttempt();

            // One message for both "no such account" and "wrong password", so
            // the form never reveals which emails are registered.
            return back()
                ->withErrors(['email' => 'Those credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->clearLoginAttempts();

        // Regenerate to prevent session fixation.
        $request->session()->regenerate();

        /*
         * Auth::login() (not setUser) is what PERSISTS the login to the
         * session. setUser() only populates the current request, so the next
         * request would be unauthenticated again.
         *
         * The guard resolves through config/auth.php, whose provider is Owner,
         * so the session serialises the owner id for us.
         */
        Auth::login($actor);
        $request->setUserResolver(static fn () => $actor);

        // The single tenancy axis for every role.
        TenantContext::set(TenantContext::resolveForActor($actor));

        // Remember me: a longer-lived cookie so the user is not logged out
        // when the session lapses.
        if ($request->boolean('remember')) {
            Auth::setRememberDuration(60 * 24 * 30);
            $request->session()->put('remember_token', $actor->getAuthIdentifier());
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        TenantContext::clear();

        return redirect()->route('login');
    }
}