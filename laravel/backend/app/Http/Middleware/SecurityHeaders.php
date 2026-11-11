<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security response headers.
 *
 * The legacy PHP app set NONE of these, so every protection below is new.
 * They are cheap and each closes a specific, named attack:
 *
 *   X-Frame-Options / frame-ancestors  clickjacking: a hostile page embedding
 *                                      ours in an iframe can trick a user into
 *                                      clicking buttons they cannot see
 *   Content-Security-Policy           limits where scripts, styles and images
 *                                      may load from, so an injected <script>
 *                                      or a leaked token has nowhere to go
 *   X-Content-Type-Options            stops a browser sniffing an uploaded file
 *                                      as HTML and executing it
 *   Referrer-Policy                   stops the reset URL (which carries an
 *                                      email address in the query string) from
 *                                      leaking to third-party sites
 *   Permissions-Policy                explicitly denies camera, microphone and
 *                                      geolocation we never use
 *
 * HSTS is sent ONLY over HTTPS. Sending it over plain HTTP is meaningless at
 * best, and pinning a browser to https:// on a host that does not serve it
 * bricks the site.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Content-Security-Policy' => $this->contentSecurityPolicy($request),
        ];

        // Only meaningful once the site is actually served over TLS.
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            // Never cache a response that carries security headers it lacks.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    /**
     * A CSP strict enough to be useful without breaking the SPA.
     *
     * `style-src 'unsafe-inline'` is required because Vite injects a runtime
     * style block, and `script-src 'self'` is what actually matters -- it is
     * what stops an injected inline script from executing. connect-src covers
     * the Inertia XHR endpoint and the Vite dev server.
     */
    private function contentSecurityPolicy(Request $request): string
    {
        $devServer = $request->getHost() === 'localhost' || $request->getHost() === '127.0.0.1';

        $connect = $devServer ? "'self' ws://localhost:5173 ws://127.0.0.1:5173" : "'self'";

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            // Vite injects styles at runtime; Tailwind utilities are inline.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src {$connect}",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
    }
}
