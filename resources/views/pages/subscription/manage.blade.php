<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\SubscriptionPlan;
use Illuminate\View\View;

new #[Title('Subscription Management')]
class extends Component {
    public $showCancelModal = false;
    public $cancellationReason = '';

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

    public function showCancelModal()
    {
        $this->showCancelModal = true;
    }

    public function cancelSubscription()
    {
        if (!$this->currentSubscription()) {
            return;
        }

        $this->currentSubscription()->cancel($this->cancellationReason);
        
        session()->flash('success', 'Your subscription has been cancelled.');
        $this->showCancelModal = false;
        $this->redirect(request()->header('Referer'));
    }

    public function currentSubscription()
    {
        return auth()->user()->institution->currentSubscription()->first();
    }

    public function plans()
    {
        return SubscriptionPlan::active()->ordered()->get();
    }
}; ?>

<div class="py-4">
    <flux:heading size="lg" class="mb-6">Subscription Management</flux:heading>

    @if($this->currentSubscription())
        {{-- Current Subscription Card --}}
        <div class="bg-white rounded-xl border border-zinc-200 p-6 mb-8">
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
                    <flux:button variant="outline" wire:click="showCancelModal">
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
            <flux:button href="{{ route('subscription.plans') }}" variant="primary">
                View Plans
            </flux:button>
        </div>
    @endif

    {{-- Cancel Modal --}}
    @if($showCancelModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">Cancel Subscription</h3>
            <p class="text-zinc-600 mb-4">
                Are you sure you want to cancel your subscription? You'll lose access to all features at the end of your current billing period.
            </p>
            
            <div class="mb-4">
                <flux:field>
                    <flux:label>Reason for cancellation (optional)</flux:label>
                    <flux:textarea wire:model="cancellationReason" placeholder="Help us improve by telling us why you're cancelling..."></flux:textarea>
                </flux:field>
            </div>
            
            <div class="flex gap-3">
                <flux:button wire:click="$set('showCancelModal', false)" variant="outline" class="flex-1">
                    Keep Subscription
                </flux:button>
                <flux:button wire:click="cancelSubscription" variant="danger" class="flex-1">
                    Cancel Subscription
                </flux:button>
            </div>
        </div>
    </div>
    @endif
</div>