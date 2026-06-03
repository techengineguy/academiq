<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Prefer the session-selected tenant (set by RequiresTenant) over the FK column.
        $institution = Tenant::current()
            ?? Institution::find($request->session()->get('active_institution_id'))
            ?? $user->institution;

        if (! $institution) {
            return redirect()->route('login');
        }

        // Check if institution has an active subscription or is on trial
        if (! $institution->hasActiveSubscription()) {
            return redirect()->route('subscription.expired');
        }

        // Check if trial is ending soon (within 3 days)
        if ($institution->isOnTrial()) {
            $subscription = $institution->currentSubscription()->first();
            if ($subscription && $subscription->trial_ends_at) {
                // Positive = days remaining, negative = already expired
                $daysRemaining = now()->diffInDays($subscription->trial_ends_at, false);

                // If trial has expired, redirect to expired page
                if ($daysRemaining < 0) {
                    return redirect()->route('subscription.expired');
                }

                // Show warning if trial expires within 3 days
                if ($daysRemaining <= 3) {
                    $roundedDays = (int) ceil($daysRemaining);
                    session()->flash('trial_warning', "Your trial expires in {$roundedDays} day(s). Please upgrade your subscription.");
                }
            }
        }

        return $next($request);
    }
}
