<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    /**
     * Block access to routes that require a specific subscription plan feature.
     *
     * Usage: middleware('plan.feature:exam_management')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        $institution = Tenant::current() ?? $user?->institution;

        if (! $user || ! $institution) {
            return redirect()->route('login');
        }

        if (! $institution->hasFeature($feature)) {
            $dashboardRoute = match ($user->role) {
                'student' => 'student.dashboard',
                'teacher' => 'teacher.dashboard',
                'parent' => 'parent.dashboard',
                default => 'dashboard',
            };

            return redirect()->route($dashboardRoute)
                ->with('error', 'This feature is not available on your current subscription plan. Please upgrade to access it.');
        }

        return $next($request);
    }
}
