<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Security hardening
|--------------------------------------------------------------------------
|
| These pin the fixes for issues found by auditing the real configuration
| rather than by reading a checklist. Each test states the attack it stops.
|
| The headline one is the JWT bridge. JWT_SECRET shipped EMPTY, and
| hash_hmac() accepts a blank key and still produces a verifiable MAC -- so
| anybody could compute a valid signature for a token of their choosing and
| impersonate any owner. It is genuinely exploitable, not theoretical.
|
*/

use App\Http\Middleware\AuthenticateLegacyJwt;
use App\Models\Owner;
use App\Support\LegacyJwt;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    // Each test declares the secret it needs; an empty one is the vulnerable
    // state and is asserted explicitly below.
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

// --- the legacy JWT bridge is GONE ---------------------------------------

/*
 * These replace the tests that pinned the bridge's behaviour. Removing a
 * security control deserves the same kind of regression test as adding one, so
 * what is asserted now is that the code cannot come back unnoticed.
 */
it('has no legacy JWT bridge left to abuse', function (): void {
    expect(class_exists(LegacyJwt::class))->toBeFalse()
        ->and(class_exists(AuthenticateLegacyJwt::class))->toBeFalse();
});

it('is not wired into the middleware stack', function (): void {
    foreach (app('router')->getMiddleware() as $alias) {
        expect($alias)->not->toBe(AuthenticateLegacyJwt::class);
    }

    // And no route still depends on it.
    foreach (app('router')->getRoutes()->getRoutes() as $route) {
        foreach ($route->gatherMiddleware() as $middleware) {
            expect($middleware)->not->toBe('legacy.jwt');
        }
    }
});

/*
 * The bypass the bridge carried: an empty JWT_SECRET produced a valid MAC, so a
 * forged token was accepted as any owner. With the bridge deleted there is no
 * code path left that reads the cookie or the secret at all.
 */
it('ignores the legacy rf_token cookie entirely', function (): void {
    $b64 = fn (string $s): string => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    $header = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = $b64(json_encode([
        'role' => 'owner', 'owner_id' => 1, 'exp' => time() + 3600,
    ]));
    $forged = $header.'.'.$payload.'.'.$b64(hash_hmac('sha256', $header.'.'.$payload, '', true));

    // No session, and the cookie alone: still a guest.
    $this->withUnencryptedCookie('rf_token', $forged)->get('/renters')->assertRedirect('/login');

    expect(app('auth')->check())->toBeFalse();
});
// --- response headers -------------------------------------------------

it('sends the baseline security headers', function (): void {
    $response = $this->get('/login');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    // Clickjacking: a hostile page must not be able to frame ours.
    $response->assertHeader('X-Frame-Options', 'DENY');
    // The reset URL carries an email address; it must not leak to third parties.
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');
});

it('sends a content security policy that forbids inline scripts', function (): void {
    $csp = $this->get('/login')->headers->get('Content-Security-Policy');

    expect($csp)->not->toBeNull()
        ->and($csp)->toContain("frame-ancestors 'none'")
        ->and($csp)->toContain("object-src 'none'")
        ->and($csp)->toContain("base-uri 'self'")
        // The actual XSS control: no inline script may run.
        ->and($csp)->toContain("script-src 'self'");
});

it('does not send HSTS over plain http', function (): void {
    // Pinning a browser to https on a host that does not serve it bricks it.
    expect($this->get('/login')->headers->get('Strict-Transport-Security'))->toBeNull();
});

it('applies the headers to error responses too', function (): void {
    $response = $this->get('/email-logs');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

// --- rate limiting ----------------------------------------------------

it('throttles the authenticated area', function (): void {
    $group = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'renters' && in_array('GET', $r->methods(), true));

    expect($group?->gatherMiddleware())->toContain('throttle:session');
});

it('throttles the expensive and mail-sending writes', function (): void {
    $middlewareFor = function (string $uri, string $method): array {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($r) => $r->uri() === $uri && in_array($method, $r->methods(), true));

        return $route?->gatherMiddleware() ?? [];
    };

    // Money in.
    expect($middlewareFor('payments', 'POST'))->toContain('throttle:writes');
    // Bulk mail: each send costs money and writes a row.
    expect($middlewareFor('complaints', 'POST'))->toContain('throttle:mail');
    expect($middlewareFor('renters', 'POST'))->toContain('throttle:mail');
    // Whole-portfolio reads.
    expect($middlewareFor('reports', 'GET'))->toContain('throttle:reads');
});

/*
 * A typo in a route's throttle:name fails only when that route is hit, and
 * fails as a runtime error rather than a clear message. Resolving each name here
 * turns that into a build-time failure instead.
 */
it('registers every named rate limiter it advertises', function (): void {
    $request = Request::create('/renters', 'GET');

    foreach (['login', 'session', 'writes', 'reads', 'mail'] as $name) {
        $resolver = app(RateLimiter::class)->limiter($name);

        expect($resolver)->toBeInstanceOf(Closure::class);

        $limit = $resolver($request);

        expect($limit)->toBeInstanceOf(Limit::class)
            ->and($limit->maxAttempts)->toBeGreaterThan(0);
    }
});

// --- hashing ----------------------------------------------------------

/*
 * The COST itself is not asserted: phpunit.xml pins BCRYPT_ROUNDS=4 so the
 * suite is not dominated by hashing, and .env pins 12. What matters here is that
 * the config exists and is wired to the environment rather than hard-coded.
 */
it('pins bcrypt with an explicit 72-byte limit', function (): void {
    expect(config('hashing.driver'))->toBe('bcrypt')
        // bcrypt silently ignores bytes past 72, so a 1MB 'password' would
        // authenticate on its first 72 bytes. The limit makes that explicit.
        ->and(config('hashing.bcrypt.limit'))->toBe(72)
        // Upgrades hashes opportunistically when the cost is raised.
        ->and(config('hashing.rehash_on_login'))->toBeTrue();
});

// --- the audit command ------------------------------------------------

/*
 * APP_DEBUG=true is the single most common way a Laravel app leaks: it renders
 * stack traces, file paths, SQL and environment values to whoever triggers an
 * error, including the secrets in this .env.
 */
it('reports a critical problem when debug is on', function (): void {
    config()->set('app.debug', true);

    $this->artisan('security:audit')->assertExitCode(1);
});

it('passes the audit when nothing critical is wrong', function (): void {
    config()->set('app.debug', false);
    config()->set('app.env', 'production');
    config()->set('session.secure', true);

    $this->artisan('security:audit')->assertExitCode(0);
});
