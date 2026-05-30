<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectUsers
{
    /**
     * Redirect non-admin users to their respective portals.
     * Applied to admin routes to prevent them from accessing the admin panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Allow profile, logout, and portal routes
        if ($request->routeIs('student.*', 'teacher.*', 'parent.*', 'logout', 'profile.*')) {
            return $next($request);
        }

        return match ($user->role) {
            'student' => redirect()->route('student.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            default => $next($request),
        };
    }
}
