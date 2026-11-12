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
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
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
        $devServer = $request->getHost() === 'localhost' || $request->getHost() === '127.0.0.1' || $request->getHost() === '::1';

        // Vite dev server port (defaults to 5173). Set VITE_PORT in .env when
        // Vite picks a different port, e.g. because 5173 is already in use.
        $vitePort = (string) env('VITE_PORT', 5173);
        // Vite dev server binds to IPv4 (127.0.0.1); CSP needs explicit host:port matches.
        $viteHosts = ['localhost', '127.0.0.1'];
        $viteOrigins = [];
        foreach ($viteHosts as $host) {
            $viteOrigins[] = "http://{$host}:{$vitePort}";
            $viteOrigins[] = "ws://{$host}:{$vitePort}";
        }

        $script = $devServer ? "script-src 'self' " . implode(' ', $viteOrigins) : "script-src 'self'";
        // Vite injects styles at runtime; Tailwind utilities are inline.
        $style = $devServer ? "style-src 'self' 'unsafe-inline' " . implode(' ', $viteOrigins) : "style-src 'self' 'unsafe-inline'";
        $connect = $devServer ? "connect-src 'self' " . implode(' ', $viteOrigins) : "connect-src 'self'";
        // In dev mode Vite re-serves npm font assets (e.g. @fontsource, FontAwesome webfonts)
        // from its own origin rather than from 'self', so the Vite origins must also be
        // allowed under font-src.
        $fontExtra = $devServer ? ' ' . implode(' ', $viteOrigins) : '';

        return implode('; ', [
            "default-src 'self'",
            $script,
            $style,
            "img-src 'self' data: blob:",
            "font-src 'self' data: https://fonts.bunny.net https://fonts.googleapis.com https://fonts.gstatic.com{$fontExtra}",
            $connect,
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
    }
}
