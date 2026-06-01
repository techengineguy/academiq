<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;

new #[Title('Checkout')]
#[Layout('layouts.guest')]
class extends Component {
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

        $institution = auth()->user()->institution;
        $existing = $institution->currentSubscription()->first();

        $endsAt = $this->billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();
        $amount = $this->plan->priceForCycle($this->billingCycle);

        if ($existing) {
            // Upgrade / renew existing subscription to the chosen plan
            $existing->update([
                'subscription_plan_id' => $this->plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'next_billing_date' => $endsAt,
                'amount' => $amount,
                'billing_cycle' => $this->billingCycle,
                'plan_features' => $this->plan->features,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);
        } else {
            // Create a fresh active subscription
            Subscription::create([
                'institution_id' => $institution->id,
                'subscription_plan_id' => $this->plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'next_billing_date' => $endsAt,
                'amount' => $amount,
                'billing_cycle' => $this->billingCycle,
                'plan_features' => $this->plan->features,
            ]);
        }

        $dashboardRoute = match (auth()->user()->role) {
            'student' => 'student.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent' => 'parent.dashboard',
            default => 'dashboard',
        };

        session()->flash('success', "You are now subscribed to the {$this->plan->name} plan.");
        $this->redirect(route($dashboardRoute));
    }

    public function cancel(): void
    {
        $this->redirect(route('subscription.manage'));
    }
}; ?>

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
