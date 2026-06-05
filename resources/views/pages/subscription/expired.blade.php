<?php

use App\Models\Institution;
use App\Models\SubscriptionPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Subscription Required')]
#[Layout('layouts.guest')]
class extends Component
{
    private function currentInstitution(): ?Institution
    {
        return Institution::find(session('active_institution_id'))
            ?? auth()->user()?->institution;
    }

    public function selectPlan($planId)
    {
        return redirect()->route('subscription.checkout', ['plan' => $planId]);
    }

    public function plans()
    {
        return SubscriptionPlan::active()->ordered()->get();
    }

    public function currentSubscription()
    {
        return $this->currentInstitution()?->currentSubscription()->first();
    }

    public function trialAvailable(): bool
    {
        $institution = $this->currentInstitution();

        return $institution && ! $institution->hasUsedTrial();
    }
}; ?>

<div class="max-w-4xl mx-auto px-6 py-12 text-center">
    {{-- Icon --}}
    <div class="mb-8">
        <div class="w-24 h-24 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <flux:icon name="exclamation-triangle" class="w-12 h-12 text-red-600 dark:text-red-400" />
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-4">Subscription Required</h1>
            @if($this->currentSubscription())
                @if($this->currentSubscription()->status === 'trial')
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-2">Your free trial has expired.</p>
                @else
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-2">Your subscription has expired.</p>
                @endif
            @else
                <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-2">You need an active subscription to access Academiq.</p>
            @endif
            <p class="text-zinc-500 dark:text-zinc-400">
                @if(in_array(auth()->user()->role, ['admin', 'accountant']) || auth()->user()->isAdmin())
                    Please choose a plan to continue using our services.
                @else
                    Please contact your administrator to renew the subscription.
                @endif
            </p>
        </div>

        {{-- Current Subscription Info --}}
        @if($this->currentSubscription())
        <div class="bg-white rounded-xl border border-zinc-200 p-6 mb-8 max-w-md mx-auto">
            <h3 class="font-semibold text-zinc-900 mb-2">Current Subscription</h3>
            <div class="text-sm text-zinc-600 space-y-1">
                <div class="flex justify-between">
                    <span>Plan:</span>
                    <span class="font-medium">{{ $this->currentSubscription()->plan->name ?? 'Unknown' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Status:</span>
                    <span class="font-medium text-red-600">{{ $this->currentSubscription()->status_label }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Expired:</span>
                    <span class="font-medium">{{ $this->currentSubscription()->ends_at->format('M j, Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'accountant']) || auth()->user()->isAdmin())
        {{-- Free Trial Card --}}
        @if($this->trialAvailable())
        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-700 p-6 mb-8 max-w-md mx-auto">
            <div class="flex items-center justify-center mb-3">
                <flux:icon name="gift" class="w-7 h-7 text-indigo-600 dark:text-indigo-400 mr-2" />
                <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-100">Start Your Free Trial</h3>
            </div>
            <p class="text-sm text-indigo-700 dark:text-indigo-300 mb-4">
                Get 14 days of full access, no payment required.
            </p>
            <flux:button variant="primary" href="{{ route('subscription.plans') }}" class="w-full button">
                Start Free Trial
            </flux:button>
        </div>
        @endif

        {{-- Plans Grid — admin/accountant only --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($this->plans() as $plan)
            <div class="bg-white rounded-xl border border-zinc-200 p-6 hover:border-indigo-300 hover:shadow-lg transition-all">
                <h3 class="text-lg font-bold text-zinc-900 mb-2">{{ $plan->name }}</h3>
                <div class="mb-4">
                    <span class="text-2xl font-bold text-zinc-900">{{ $plan->formatted_price }}</span>
                    <span class="text-zinc-600 text-sm">{{ $plan->billing_cycle_label }}</span>
                </div>
                <p class="text-sm text-zinc-600 mb-4">{{ $plan->description }}</p>

                <flux:button
                    wire:click="selectPlan({{ $plan->id }})"
                    variant="primary"
                    class="w-full"
                >
                    Choose {{ $plan->name }}
                </flux:button>
            </div>
            @endforeach
        </div>

        {{-- Contact Support --}}
        <div class="text-center">
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                Need help choosing a plan or have questions?
            </p>
            <flux:button variant="outline" href="mailto:support@academiqedu.com">
                Contact Support
            </flux:button>
        </div>
        @endif
    </div>
</div>