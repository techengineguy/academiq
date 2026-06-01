<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSubscriptionAccess
{
    /** Roles that may access subscription management pages. */
    private const ALLOWED_ROLES = ['admin', 'accountant'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $isAllowed = in_array($user->role, self::ALLOWED_ROLES, true)
            || $user->isAdmin()
            || $user->hasRole('accountant');

        if (! $isAllowed) {
            $dashboardRoute = match ($user->role) {
                'student' => 'student.dashboard',
                'teacher' => 'teacher.dashboard',
                'parent' => 'parent.dashboard',
                default => 'dashboard',
            };

            return redirect()->route($dashboardRoute)
                ->with('error', 'You do not have permission to manage subscriptions.');
        }

        return $next($request);
    }
}
