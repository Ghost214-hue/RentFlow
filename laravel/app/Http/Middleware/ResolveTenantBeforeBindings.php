<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Resolve the owner tenancy context, then perform route-model binding.
 *
 * This replaces Laravel's SubstituteBindings in the web group so the two run
 * in the correct order. SubstituteBindings on its own is too early: it resolves
 * a {model} parameter before the route's middleware (and therefore before
 * authentication) has run, and every owner-scoped model throws when no tenancy
 * context exists. A parameterised route such as
 * POST /complaints/{complaint}/advance would 500 during binding.
 *
 * Resolving the actor first makes those routes work without forcing every
 * controller to set its own context.
 */
class ResolveTenantBeforeBindings
{
    public function __construct(
        private readonly ResolveTenantContext $resolveTenantContext,
        private readonly SubstituteBindings $bindings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // ResolveTenantContext never short-circuits the pipeline, so the stub
        // closure below is never invoked; it exists only to satisfy its
        // signature.
        $this->resolveTenantContext->handle($request, static fn (): SymfonyResponse => new SymfonyResponse);

        return $this->bindings->handle($request, $next);
    }
}