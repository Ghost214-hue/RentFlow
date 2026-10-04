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

use App\Models\Owner;
use App\Support\LegacyJwt;
use App\Support\MissingJwtSecret;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Sets JWT_SECRET for one test.
 *
 * env() does not read $_ENV directly -- it goes through the repository's
 * adapters, which check getenv() and $_SERVER as well. Writing only $_ENV left
 * the old value in place and the assertions were meaningless.
 */
function setJwtSecret(string $value): void
{
    putenv('JWT_SECRET='.$value);
    $_ENV['JWT_SECRET'] = $value;
    $_SERVER['JWT_SECRET'] = $value;
}

beforeEach(function (): void {
    // Each test declares the secret it needs; an empty one is the vulnerable
    // state and is asserted explicitly below.
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

// --- the JWT bridge ---------------------------------------------------

/*
 * THE HEADLINE FIX.
 *
 * With no secret, the bridge must refuse EVERY token rather than accept every
 * token. "Rejects everything" and "accepts anything" are one character apart.
 */
it('refuses every legacy token when the shared secret is empty', function (): void {
    $b64 = fn (string $s): string => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    $header = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = $b64(json_encode([
        'role' => 'owner', 'owner_id' => 1, 'actor_id' => 1,
        'exp' => time() + 3600,
    ]));

    // Exactly what an attacker would send, signed with the empty key.
    $forged = $header.'.'.$payload.'.'.$b64(hash_hmac('sha256', $header.'.'.$payload, '', true));

    setJwtSecret('');

    expect(LegacyJwt::decode($forged))->toBeNull()
        ->and(LegacyJwt::hasSecret())->toBeFalse();
});

it('reports whether the bridge is usable', function (): void {
    setJwtSecret('');
    expect(LegacyJwt::hasSecret())->toBeFalse();

    setJwtSecret(str_repeat('a', 32));
    expect(LegacyJwt::hasSecret())->toBeTrue();
});

it('still refuses a wrong secret', function (): void {
    $b64 = fn (string $s): string => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    $header = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = $b64(json_encode(['role' => 'owner', 'owner_id' => 1, 'exp' => time() + 3600]));

    setJwtSecret(str_repeat('a', 32));
    $wrong = $header.'.'.$payload.'.'.$b64(hash_hmac('sha256', $header.'.'.$payload, str_repeat('b', 32), true));

    expect(LegacyJwt::decode($wrong))->toBeNull();
});

it('accepts a correctly signed token when a real secret is set', function (): void {
    $secret = str_repeat('k', 40);
    setJwtSecret($secret);

    $b64 = fn (string $s): string => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    $header = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = $b64(json_encode(['role' => 'owner', 'owner_id' => 1, 'exp' => time() + 3600]));
    $token = $header.'.'.$payload.'.'.$b64(hash_hmac('sha256', $header.'.'.$payload, $secret, true));

    expect(LegacyJwt::decode($token))->not->toBeNull();
});

it('still refuses an expired token', function (): void {
    $secret = str_repeat('k', 40);
    setJwtSecret($secret);

    $b64 = fn (string $s): string => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    $header = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = $b64(json_encode(['role' => 'owner', 'owner_id' => 1, 'exp' => time() - 10]));
    $token = $header.'.'.$payload.'.'.$b64(hash_hmac('sha256', $header.'.'.$payload, $secret, true));

    expect(LegacyJwt::decode($token))->toBeNull();
});

it('names the missing secret clearly when one is demanded', function (): void {
    setJwtSecret('');

    $r = new ReflectionMethod(LegacyJwt::class, 'secret');
    $r->setAccessible(true);

    expect(fn () => $r->invoke(null))
        ->toThrow(MissingJwtSecret::class, 'jwt:secret');
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

it('reports a critical problem when the JWT secret is empty', function (): void {
    setJwtSecret('');

    $this->artisan('security:audit')->assertExitCode(1);
});

it('passes the audit when nothing critical is wrong', function (): void {
    config()->set('app.debug', false);
    config()->set('app.env', 'production');
    config()->set('session.secure', true);
    setJwtSecret(str_repeat('z', 40));

    $this->artisan('security:audit')->assertExitCode(0);
});
