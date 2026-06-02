<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Validate Paystack signature
        $signature = $request->header('x-paystack-signature', '');
        $secret = config('services.paystack.secret');
        $computed = hash_hmac('sha512', $request->getContent(), $secret);

        if (! hash_equals($computed, $signature)) {
            Log::warning('Paystack webhook signature mismatch');

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = $request->json('event', '');
        $data = $request->json('data', []);

        match ($event) {
            'charge.success' => $this->handleChargeSuccess($data),
            'subscription.disable' => $this->handleSubscriptionDisable($data),
            'invoice.payment_failed' => $this->handlePaymentFailed($data),
            default => null,
        };

        return response()->json(['message' => 'ok']);
    }

    private function handleChargeSuccess(array $data): void
    {
        $subCode = $data['subscription']['subscription_code'] ?? null;

        if (! $subCode) {
            return;
        }

        $subscription = Subscription::where('paystack_subscription_code', $subCode)->first();

        if (! $subscription) {
            return;
        }

        $endsAt = $subscription->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $subscription->update([
            'status' => 'active',
            'ends_at' => $endsAt,
            'next_billing_date' => $endsAt,
            'paystack_reference' => $data['reference'] ?? $subscription->paystack_reference,
        ]);
    }

    private function handleSubscriptionDisable(array $data): void
    {
        $subCode = $data['subscription_code'] ?? null;

        if (! $subCode) {
            return;
        }

        Subscription::where('paystack_subscription_code', $subCode)->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Subscription disabled on Paystack.',
        ]);
    }

    private function handlePaymentFailed(array $data): void
    {
        $subCode = $data['subscription']['subscription_code'] ?? null;

        if (! $subCode) {
            return;
        }

        Subscription::where('paystack_subscription_code', $subCode)->update([
            'status' => 'past_due',
        ]);
    }
}
