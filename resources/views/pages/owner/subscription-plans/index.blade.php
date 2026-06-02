<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\SubscriptionPlan;
use Flux\Flux;

new
#[Title('Subscription Plans')]
#[Layout('layouts.owner')]
class extends Component {

    public ?int $editingId = null;
    public string $editName = '';
    public string $editDescription = '';
    public string $editPrice = '';
    public bool $editIsActive = true;
    public string $editPaystackMonthlyCode = '';
    public string $editPaystackYearlyCode = '';

    #[Computed]
    public function plans()
    {
        return SubscriptionPlan::orderBy('sort_order')->orderBy('price')->get();
    }

    public function startEdit(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $this->editingId = $id;
        $this->editName = $plan->name;
        $this->editDescription = (string) ($plan->description ?? '');
        $this->editPrice = (string) $plan->price;
        $this->editIsActive = (bool) $plan->is_active;
        $this->editPaystackMonthlyCode = (string) ($plan->paystack_monthly_plan_code ?? '');
        $this->editPaystackYearlyCode = (string) ($plan->paystack_yearly_plan_code ?? '');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->reset([
            'editName', 'editDescription', 'editPrice',
            'editIsActive', 'editPaystackMonthlyCode', 'editPaystackYearlyCode',
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editDescription' => ['nullable', 'string'],
            'editPrice' => ['required', 'numeric', 'min:0'],
            'editPaystackMonthlyCode' => ['nullable', 'string', 'max:255'],
            'editPaystackYearlyCode' => ['nullable', 'string', 'max:255'],
        ]);

        SubscriptionPlan::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'description' => $this->editDescription !== '' ? $this->editDescription : null,
            'price' => $this->editPrice,
            'is_active' => $this->editIsActive,
            'paystack_monthly_plan_code' => $this->editPaystackMonthlyCode !== '' ? $this->editPaystackMonthlyCode : null,
            'paystack_yearly_plan_code' => $this->editPaystackYearlyCode !== '' ? $this->editPaystackYearlyCode : null,
        ]);

        unset($this->plans);
        $this->cancelEdit();

        Flux::toast(variant: 'success', text: __('Plan updated successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Subscription Plans') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Manage plans and link them to their Paystack plan codes for recurring billing.') }}
        </p>
    </div>

    <div class="space-y-4">
        @foreach($this->plans as $plan)
            <flux:card>
                @if($editingId === $plan->id)
                    <form wire:submit="save" class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input label="{{ __('Plan Name') }}" wire:model="editName" required />
                            <flux:input label="{{ __('Monthly Price (₦)') }}" type="text" inputmode="decimal" wire:model="editPrice" required />
                        </div>

                        <flux:textarea label="{{ __('Description') }}" wire:model="editDescription" rows="2" />

                        <div class="rounded-lg border border-dashed border-gray-300 p-4 dark:border-zinc-600 space-y-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Paystack Plan Codes') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Find these in your Paystack dashboard under Products → Plans. Format: PLN_xxxxxxxx') }}
                                </p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <flux:input
                                    label="{{ __('Monthly Plan Code') }}"
                                    wire:model="editPaystackMonthlyCode"
                                    placeholder="PLN_xxxxxxxxxxxxxxx"
                                />
                                <flux:input
                                    label="{{ __('Yearly Plan Code') }}"
                                    wire:model="editPaystackYearlyCode"
                                    placeholder="PLN_xxxxxxxxxxxxxxx"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <flux:switch wire:model="editIsActive" label="{{ __('Active') }}" />
                        </div>

                        <div class="flex gap-3">
                            <flux:button type="submit" variant="primary" size="sm" class="button">{{ __('Save') }}</flux:button>
                            <flux:button type="button" variant="subtle" size="sm" wire:click="cancelEdit">{{ __('Cancel') }}</flux:button>
                        </div>
                    </form>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 space-y-3">
                            <div class="flex items-center gap-3">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h2>
                                <flux:badge :color="$plan->is_active ? 'green' : 'gray'" size="sm">
                                    {{ $plan->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </div>

                            @if($plan->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                            @endif

                            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $plan->formatted_price }}</span>
                                    {{ __('/ month') }}
                                </span>
                                <span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $plan->formattedPriceForCycle('yearly') }}</span>
                                    {{ __('/ year (20% off)') }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-3 text-xs">
                                <div class="flex items-center gap-1.5">
                                    @if($plan->paystack_monthly_plan_code)
                                        <flux:icon name="check-circle" class="h-4 w-4 text-green-500" />
                                        <span class="font-mono text-gray-700 dark:text-gray-300">
                                            {{ __('Monthly:') }} {{ $plan->paystack_monthly_plan_code }}
                                        </span>
                                    @else
                                        <flux:icon name="exclamation-circle" class="h-4 w-4 text-amber-500" />
                                        <span class="text-amber-600 dark:text-amber-400">{{ __('No monthly Paystack code') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if($plan->paystack_yearly_plan_code)
                                        <flux:icon name="check-circle" class="h-4 w-4 text-green-500" />
                                        <span class="font-mono text-gray-700 dark:text-gray-300">
                                            {{ __('Yearly:') }} {{ $plan->paystack_yearly_plan_code }}
                                        </span>
                                    @else
                                        <flux:icon name="exclamation-circle" class="h-4 w-4 text-amber-500" />
                                        <span class="text-amber-600 dark:text-amber-400">{{ __('No yearly Paystack code') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <flux:button size="sm" variant="subtle" icon="pencil" wire:click="startEdit({{ $plan->id }})">
                            {{ __('Edit') }}
                        </flux:button>
                    </div>
                @endif
            </flux:card>
        @endforeach

        @if($this->plans->isEmpty())
            <flux:card>
                <div class="p-6 text-center">
                    <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Plans') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Seed or create subscription plans to get started.') }}</p>
                </div>
            </flux:card>
        @endif
    </div>
</div>
