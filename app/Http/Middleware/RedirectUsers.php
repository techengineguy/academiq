<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectUsers
{
    /**
     * Redirect student and teacher users to their respective portals.
     * Applied to admin routes to prevent them from accessing the admin panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Allow profile and logout routes
        if ($request->routeIs('student.*', 'teacher.*', 'logout', 'profile.*')) {
            return $next($request);
        }

        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        if ($user->role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        }

        return $next($request);
    }
}
