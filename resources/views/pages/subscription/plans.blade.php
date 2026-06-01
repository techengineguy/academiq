<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Support\Str;

new #[Title('Choose Your Plan')]
#[Layout('layouts.guest')]
class extends Component {
    public $selectedPlan = null;
    public $billingCycle = 'monthly';

    public function mount()
    {
        $institution = auth()->user()->institution;

        // If user already has an active subscription, redirect to manage
        if ($institution->hasActiveSubscription()) {
            return redirect()->route('subscription.manage');
        }

        // If they've already used their free trial, send them to checkout a paid plan
        if ($institution->hasUsedTrial()) {
            return redirect()->route('subscription.expired');
        }
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = $planId;
    }

    public function setBillingCycle($cycle)
    {
        $this->billingCycle = $cycle;
    }

    public function subscribe()
    {
        if (!$this->selectedPlan) {
            $this->addError('plan', 'Please select a subscription plan.');
            return;
        }

        $plan = SubscriptionPlan::find($this->selectedPlan);
        $institution = auth()->user()->institution;

        // Block second trial
        if ($institution->hasUsedTrial()) {
            $this->addError('plan', 'Your free trial has already been used. Please select a paid plan.');

            return redirect()->route('subscription.expired');
        }

        // Create subscription with 14-day trial
        $subscription = Subscription::create([
            'institution_id' => $institution->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
            'amount' => $plan->price,
            'billing_cycle' => $this->billingCycle,
            'plan_features' => $plan->features,
        ]);

        session()->flash('success', 'Your 14-day free trial has started! Welcome to Academiq.');
        
        return redirect()->route('dashboard');
    }

    public function plans()
    {
        return SubscriptionPlan::active()->ordered()->get();
    }
}; ?>

<div class="max-w-7xl mx-auto px-6 py-12">
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-4">Choose Your Plan</h1>
        <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
            Start with a 14-day free trial. No credit card required. Cancel anytime.
        </p>
    </div>

        {{-- Billing Toggle --}}
        <div class="flex justify-center mb-8">
            <div class="bg-white rounded-xl p-1 shadow-sm border border-zinc-200">
                <button 
                    wire:click="setBillingCycle('monthly')"
                    class="px-6 py-2 rounded-lg text-sm font-semibold transition-all {{ $billingCycle === 'monthly' ? 'bg-indigo-600 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-900' }}"
                >
                    Monthly
                </button>
                <button 
                    wire:click="setBillingCycle('yearly')"
                    class="px-6 py-2 rounded-lg text-sm font-semibold transition-all {{ $billingCycle === 'yearly' ? 'bg-indigo-600 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-900' }}"
                >
                    Yearly <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full ml-1">Save 20%</span>
                </button>
            </div>
        </div>

        {{-- Plans Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            @foreach($this->plans() as $plan)
            <div class="relative bg-white rounded-2xl border-2 transition-all duration-300 {{ $selectedPlan == $plan->id ? 'border-indigo-500 shadow-xl shadow-indigo-500/20' : 'border-zinc-200 hover:border-indigo-300 hover:shadow-lg' }}">
                {{-- Popular Badge --}}
                @if($plan->slug === 'professional')
                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                    <span class="bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded-full">Most Popular</span>
                </div>
                @endif

                <div class="p-8">
                    {{-- Plan Header --}}
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">{{ $plan->name }}</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4">{{ $plan->description }}</p>
                        <div class="mb-4">
                            <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ $plan->formatted_price }}</span>
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $plan->billing_cycle_label }}</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-3 mb-8">
                        @foreach($plan->features as $feature)
                        <li class="flex items-center gap-3">
                            <flux:icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0" />
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>

                    {{-- Limits --}}
                    <div class="border-t border-zinc-100 dark:border-zinc-700 pt-6 mb-6">
                        <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <div class="flex justify-between">
                                <span>Students:</span>
                                <span class="font-semibold">{{ $plan->isUnlimitedStudents() ? 'Unlimited' : number_format($plan->max_students) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Teachers:</span>
                                <span class="font-semibold">{{ $plan->isUnlimitedTeachers() ? 'Unlimited' : number_format($plan->max_teachers) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Staff:</span>
                                <span class="font-semibold">{{ $plan->isUnlimitedStaff() ? 'Unlimited' : number_format($plan->max_staff) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Select Button --}}
                    <flux:button 
                        wire:click="selectPlan({{ $plan->id }})"
                        variant="{{ $selectedPlan == $plan->id ? 'primary' : 'outline' }}"
                        class="w-full"
                    >
                        {{ $selectedPlan == $plan->id ? 'Selected' : 'Select Plan' }}
                    </flux:button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Action Buttons --}}
        <div class="text-center">
            @error('plan')
                <div class="mb-4 text-red-600 text-sm">{{ $message }}</div>
            @enderror
            
            <flux:button
                wire:click="subscribe"
                variant="primary"
                :disabled="!$selectedPlan"
                class="px-10"
            >
                Start 14-Day Free Trial
            </flux:button>
            
            <p class="text-xs text-zinc-500 mt-4">
                No credit card required • Cancel anytime • Full access during trial
            </p>
        </div>
    </div>
</div>