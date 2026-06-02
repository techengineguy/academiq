<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $reference = $request->query('reference', '');

        if (! $reference) {
            return redirect()->route('subscription.plans')
                ->with('error', __('Invalid payment reference.'));
        }

        // Verify the transaction with Paystack
        $response = Http::withToken(config('services.paystack.secret'))
            ->get(config('services.paystack.base_url').'/transaction/verify/'.rawurlencode($reference));

        if (! $response->successful() || $response->json('data.status') !== 'success') {
            Log::warning('Paystack verification failed', [
                'reference' => $reference,
                'response' => $response->json(),
            ]);

            return redirect()->route('subscription.plans')
                ->with('error', __('Payment verification failed. Please contact support.'));
        }

        $data = $response->json('data');
        $meta = $data['metadata'] ?? [];

        $institutionId = $meta['institution_id'] ?? null;
        $planId = $meta['subscription_plan_id'] ?? null;
        $billingCycle = $meta['billing_cycle'] ?? 'monthly';

        if (! $institutionId || ! $planId) {
            Log::error('Paystack callback missing metadata', ['reference' => $reference, 'meta' => $meta]);

            return redirect()->route('subscription.plans')
                ->with('error', __('Subscription activation failed. Please contact support.'));
        }

        $plan = SubscriptionPlan::findOrFail($planId);

        $endsAt = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $existing = Subscription::where('institution_id', $institutionId)
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->latest()
            ->first();

        $paystackSub = $data['subscription'] ?? [];

        $paystackFields = [
            'paystack_subscription_code' => $paystackSub['subscription_code'] ?? null,
            'paystack_email_token' => $paystackSub['email_token'] ?? null,
            'paystack_customer_code' => $data['customer']['customer_code'] ?? null,
            'paystack_reference' => $reference,
        ];

        if ($existing) {
            $existing->update(array_merge([
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'next_billing_date' => $endsAt,
                'amount' => $plan->priceForCycle($billingCycle),
                'billing_cycle' => $billingCycle,
                'plan_features' => $plan->features,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ], $paystackFields));
        } else {
            Subscription::create(array_merge([
                'institution_id' => $institutionId,
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'next_billing_date' => $endsAt,
                'amount' => $plan->priceForCycle($billingCycle),
                'billing_cycle' => $billingCycle,
                'plan_features' => $plan->features,
            ], $paystackFields));
        }

        $dashboardRoute = match (auth()->user()?->role) {
            'student' => 'student.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent' => 'parent.dashboard',
            default => 'dashboard',
        };

        return redirect()->route($dashboardRoute)
            ->with('success', "You are now subscribed to the {$plan->name} plan.");
    }
}
