<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Symfony\Component\HttpFoundation\Response;

class RequiresTenant
{
    public function __construct(protected TenantFinder $finder) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->finder->findForRequest($request);

        if (! $tenant) {
            abort(403, 'No institution is associated with your account. Please contact support.');
        }

        $tenant->makeCurrent();

        return $next($request);
    }
}
