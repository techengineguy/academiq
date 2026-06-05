<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Institution;
use App\Models\SubscriptionPlan;
use Flux\Flux;
use Illuminate\View\View;

new #[Title('Subscription Management')]
class extends Component {
    public string $cancellationReason = '';

    public function rendering(View $view): void
    {
        $layout = match (auth()->user()->role) {
            'accountant' => 'layouts.accountant',
            default => 'layouts.app',
        };

        $view->layout($layout);
    }

    public function changePlan($planId)
    {
        return redirect()->route('subscription.checkout', ['plan' => $planId]);
    }

    public function openCancelModal(): void
    {
        Flux::modal('cancel-subscription')->show();
    }

    public function cancelSubscription()
    {
        $subscription = $this->currentSubscription();

        if (! $subscription) {
            return;
        }

        $subscription->cancel($this->cancellationReason);

        $this->cancellationReason = '';

        Flux::modal('cancel-subscription')->close();
        Flux::toast(variant: 'success', text: 'Your subscription has been cancelled.');
    }

    public function currentSubscription()
    {
        $institution = Institution::find(session('active_institution_id'))
            ?? auth()->user()?->institution;

        return $institution?->currentSubscription()->first();
    }

    public function plans()
    {
        return SubscriptionPlan::active()->ordered()->get();
    }
}; ?>

<div class="py-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Subscription Management') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage your subscription plans and billing information.') }}</p>
        </div>
    </div>

    @if($this->currentSubscription())
        {{-- Current Subscription Card --}}
        <div class="bg-white rounded-xl border border-zinc-200 p-6 mt-4 mb-8">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900">Current Plan</h3>
                    <p class="text-zinc-600">{{ $this->currentSubscription()->plan->name ?? 'Unknown Plan' }}</p>
                </div>
                <flux:badge :color="$this->currentSubscription()->status_color">
                    {{ $this->currentSubscription()->status_label }}
                </flux:badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <div class="text-2xl font-bold text-zinc-900">{{ $this->currentSubscription()->plan->formatted_price ?? '₦0.00' }}</div>
                    <div class="text-sm text-zinc-600">{{ $this->currentSubscription()->plan->billing_cycle_label ?? 'per month' }}</div>
                </div>
                <div>
                    <div class="text-sm text-zinc-600">Next Billing</div>
                    <div class="font-semibold text-zinc-900">
                        @if($this->currentSubscription()->isTrial())
                            Trial ends {{ $this->currentSubscription()->trial_ends_at->format('M j, Y') }}
                        @else
                            {{ $this->currentSubscription()->next_billing_date?->format('M j, Y') ?? 'N/A' }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-zinc-600">Status</div>
                    <div class="font-semibold text-zinc-900">
                        @if($this->currentSubscription()->isTrial())
                            {{ abs(ceil($this->currentSubscription()->daysUntilExpiry())) }} days left in trial
                        @elseif($this->currentSubscription()->isActive())
                            Active until {{ $this->currentSubscription()->ends_at->format('M j, Y') }}
                        @else
                            {{ $this->currentSubscription()->status_label }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Plan Features --}}
            @if($this->currentSubscription()->plan_features)
            <div class="border-t border-zinc-100 pt-4 mb-6">
                <h4 class="font-semibold text-zinc-900 mb-3">Plan Features</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($this->currentSubscription()->plan_features as $feature)
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="w-4 h-4 text-emerald-500" />
                        <span class="text-sm text-zinc-700">{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex gap-3">
                @if($this->currentSubscription()->hasAccess())
                    <flux:button variant="outline" wire:click="openCancelModal">
                        Cancel Subscription
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Available Plans --}}
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">Available Plans</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($this->plans() as $plan)
                <div class="bg-white rounded-xl border border-zinc-200 p-6 {{ $this->currentSubscription()->subscription_plan_id == $plan->id ? 'ring-2 ring-indigo-500' : '' }}">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-zinc-900">{{ $plan->name }}</h4>
                        @if($this->currentSubscription()->subscription_plan_id == $plan->id)
                            <flux:badge color="blue">Current</flux:badge>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <span class="text-xl font-bold text-zinc-900">{{ $plan->formatted_price }}</span>
                        <span class="text-zinc-600 text-sm">{{ $plan->billing_cycle_label }}</span>
                    </div>
                    
                    <p class="text-sm text-zinc-600 mb-4">{{ $plan->description }}</p>
                    
                    @if($this->currentSubscription()->subscription_plan_id != $plan->id)
                        <flux:button 
                            wire:click="changePlan({{ $plan->id }})"
                            variant="outline"
                            size="sm"
                            class="w-full"
                        >
                            Switch to {{ $plan->name }}
                        </flux:button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- No Subscription --}}
        <div class="text-center py-12">
            <flux:icon name="exclamation-triangle" class="w-16 h-16 text-yellow-500 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-zinc-900 mb-2">No Active Subscription</h3>
            <p class="text-zinc-600 mb-6">You don't have an active subscription. Choose a plan to get started.</p>
            <flux:button href="{{ route('subscription.plans') }}" variant="primary" class="button">
                View Plans
            </flux:button>
        </div>
    @endif

    {{-- Cancel Modal --}}
    <flux:modal name="cancel-subscription" class="min-w-[22rem] max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Cancel Subscription</flux:heading>
                <flux:text class="mt-1">
                    Are you sure? You'll lose access to all features at the end of your current billing period.
                </flux:text>
            </div>

            <flux:field>
                <flux:label>Reason for cancellation (optional)</flux:label>
                <flux:textarea wire:model="cancellationReason" placeholder="Help us improve by telling us why you're cancelling..." rows="3" />
            </flux:field>

            <div class="flex gap-3 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Keep Subscription</flux:button>
                </flux:modal.close>
                <flux:button wire:click="cancelSubscription" variant="danger">
                    Cancel Subscription
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>