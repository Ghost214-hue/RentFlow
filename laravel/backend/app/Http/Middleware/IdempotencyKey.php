<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class IdempotencyKey
{
    /** @var string The cache key prefix for idempotency keys */
    const CACHE_PREFIX = 'idempotency_key:';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply idempotency for payment-related POST/PUT requests
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT')) {
            return $next($request);
        }

        // Check for Idempotency-Key header
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            // No idempotency key provided - proceed normally
            return $next($request);
        }

        // Normalise the key (trim, lowercase, remove whitespace)
        $normalisedKey = strtolower(trim($key));

        // Compute request hash from method + path + body
        $requestHash = md5($request->method().$request->getPathInfo().$request->getContent());

        // Check for existing idempotency key
        $cacheKey = self::CACHE_PREFIX.$normalisedKey;
        $existingResponse = Cache::remember($cacheKey, now()->addHours(30), function () {
            return null;
        });

        if ($existingResponse !== null) {
            // Key and body match - return the cached response
            if (isset($existingResponse['response']) && isset($existingResponse['status'])) {
                return response($existingResponse['response'], $existingResponse['status'])
                    ->header('Idempotency-Key', $normalisedKey);
            }
        }

        // No matching idempotency key found - proceed with request
        // Wrap the response to cache it
        $response = $next($request);

        // Only cache non-error responses
        if ($response->status() >= 200 && $response->status() < 400) {
            // Cache the response body and status
            Cache::put($cacheKey, [
                'response' => $response->original,
                'status' => $response->status(),
            ], now()->addHours(30));
        }

        // Add idempotency key to response header
        return $response->header('Idempotency-Key', $normalisedKey);
    }
}