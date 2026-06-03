<?php

use App\Models\SubscriptionPlan;
use Flux\Flux;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Checkout')]
#[Layout('layouts.guest')]
class extends Component
{
    public ?SubscriptionPlan $plan = null;

    public string $billingCycle = 'monthly';

    public function mount(): void
    {
        $planParam = request()->query('plan', '');

        $this->plan = SubscriptionPlan::where('is_active', true)
            ->where(fn ($q) => $q->where('slug', $planParam)->orWhere('id', $planParam))
            ->first();

        if (! $this->plan) {
            session()->flash('error', 'The selected plan could not be found.');
            $this->redirect(route('subscription.expired'));
        }
    }

    public function confirm(): void
    {
        if (! $this->plan) {
            return;
        }

        $planCode = $this->plan->paystackPlanCode($this->billingCycle);

        if (! $planCode) {
            Flux::toast(
                variant: 'danger',
                text: __('This plan is not yet configured for online payments. Please contact support.')
            );

            return;
        }

        $amount = $this->plan->priceForCycle($this->billingCycle);
        $amountInKobo = (int) round($amount * 100);

        $response = Http::withToken(config('services.paystack.secret'))
            ->post(config('services.paystack.base_url').'/transaction/initialize', [
                'email' => auth()->user()->email,
                'amount' => $amountInKobo,
                'plan' => $planCode,
                'callback_url' => route('subscription.callback'),
                'metadata' => [
                    'institution_id' => session('active_institution_id') ?? auth()->user()->institution_id,
                    'subscription_plan_id' => $this->plan->id,
                    'billing_cycle' => $this->billingCycle,
                    'user_id' => auth()->id(),
                ],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Paystack initialize failed', [
                'response' => $response->json(),
                'plan_id' => $this->plan->id,
            ]);

            Flux::toast(
                variant: 'danger',
                text: __('Payment initialization failed. Please try again.')
            );

            return;
        }

        $this->redirect($response->json('data.authorization_url'));
    }

    public function cancel(): void
    {
        $this->redirect(route('subscription.manage'));
    }
};
?>
<div class="max-w-lg mx-auto px-6 py-12">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Confirm Subscription</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Review your plan before confirming.</p>
    </div>

    @if ($this->plan)
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $this->plan->name }}</h2>
                <flux:badge color="indigo">Selected</flux:badge>
            </div>

            <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4">{{ $this->plan->description }}</p>

            {{-- Billing cycle toggle --}}
            <div class="flex gap-2 mb-4">
                <flux:button
                    wire:click="$set('billingCycle', 'monthly')"
                    :variant="$billingCycle === 'monthly' ? 'primary' : 'ghost'"
                    size="sm"
                >
                    Monthly
                </flux:button>
                <flux:button
                    wire:click="$set('billingCycle', 'yearly')"
                    :variant="$billingCycle === 'yearly' ? 'primary' : 'ghost'"
                    size="sm"
                >
                    Yearly <span class="text-xs opacity-75 ml-1">Save 20%</span>
                </flux:button>
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                    <span>Plan</span>
                    <span class="font-medium text-zinc-900 dark:text-white">{{ $this->plan->name }}</span>
                </div>
                <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                    <span>Billing Cycle</span>
                    <span class="font-medium text-zinc-900 dark:text-white capitalize">{{ $billingCycle }}</span>
                </div>
                @if ($billingCycle === 'yearly')
                    <div class="flex justify-between text-sm text-zinc-500 dark:text-zinc-400 mb-1">
                        <span>Monthly price</span>
                        <span>{{ $this->plan->formatted_price }} &times; 12</span>
                    </div>
                    <div class="flex justify-between text-sm text-emerald-600 dark:text-emerald-400 mb-2">
                        <span>20% yearly discount</span>
                        <span>- ₦{{ number_format($this->plan->price * 12 * 0.2, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-semibold text-zinc-900 dark:text-white text-base pt-2 border-t border-zinc-100 dark:border-zinc-700">
                    <span>Total</span>
                    <span>{{ $this->plan->formattedPriceForCycle($billingCycle) }} / {{ $billingCycle === 'yearly' ? 'year' : 'month' }}</span>
                </div>
            </div>

            @if ($this->plan->features)
                <ul class="mt-4 space-y-1">
                    @foreach ($this->plan->features as $feature)
                        <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <flux:icon name="check-circle" class="w-4 h-4 text-green-500 shrink-0" />
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex gap-3">
            <flux:button wire:click="confirm" variant="primary" class="flex-1">
                Confirm Subscription
            </flux:button>
            <flux:button wire:click="cancel" variant="ghost">
                Back
            </flux:button>
        </div>
    @endif
</div>
