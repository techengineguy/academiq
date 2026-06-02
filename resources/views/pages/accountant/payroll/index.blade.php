<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Payroll;
use App\Models\PayrollAllowance;
use App\Models\PayrollDeduction;
use Illuminate\Support\Facades\Auth;

new
#[Title('Payroll')]
#[Layout('layouts.accountant')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function payrolls()
    {
        return Payroll::where('tenant_id', Auth::user()->tenant_id)
            ->with(['user', 'processedBy'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'payrollsPage');
    }

    #[Computed]
    public function allowances()
    {
        return PayrollAllowance::where('tenant_id', Auth::user()->tenant_id)
            ->with(['payroll.user'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'allowancesPage');
    }

    #[Computed]
    public function deductions()
    {
        return PayrollDeduction::where('tenant_id', Auth::user()->tenant_id)
            ->with(['payroll.user'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'deductionsPage');
    }

    #[Computed]
    public function totalNetSalary(): float
    {
        return (float) Payroll::where('tenant_id', Auth::user()->tenant_id)->sum('net_salary');
    }

    #[Computed]
    public function totalAllowances(): float
    {
        return (float) PayrollAllowance::where('tenant_id', Auth::user()->tenant_id)->sum('amount');
    }

    #[Computed]
    public function totalDeductions(): float
    {
        return (float) PayrollDeduction::where('tenant_id', Auth::user()->tenant_id)->sum('amount');
    }
};
?>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Payroll') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage payroll records, allowances, and deductions in one place.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-payroll')" icon="plus">
            {{ __('New Payroll') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Payroll Records') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->payrolls->total()) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Net Salary Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalNetSalary, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Allowances Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalAllowances, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Deductions Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalDeductions, 2) }}</p>
        </flux:card>
    </div>

    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="payrolls" icon="banknotes">
                {{ __('Payrolls') }}
                @if($this->payrolls->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->payrolls->total() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="allowances" icon="plus-circle">
                {{ __('Allowances') }}
                @if($this->allowances->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->allowances->total() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="deductions" icon="minus-circle">
                {{ __('Deductions') }}
                @if($this->deductions->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->deductions->total() }}</flux:badge>
                @endif
            </flux:tab>
        </flux:tabs>

        <flux:tab.panel name="payrolls">
            <flux:card>
                @if($this->payrolls->count())
                    <flux:table :paginate="$this->payrolls">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Employee') }}</flux:table.column>
                            <flux:table.column>{{ __('Month') }}</flux:table.column>
                            <flux:table.column>{{ __('Basic Salary') }}</flux:table.column>
                            <flux:table.column>{{ __('Allowances') }}</flux:table.column>
                            <flux:table.column>{{ __('Deductions') }}</flux:table.column>
                            <flux:table.column>{{ __('Tax') }}</flux:table.column>
                            <flux:table.column>{{ __('Net Salary') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column>{{ __('Payment Date') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->payrolls as $payroll)
                            <flux:table.rows>
                                <flux:table.row :key="$payroll->id">
                                    <flux:table.cell>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ $payroll->user?->first_name }} {{ $payroll->user?->last_name }}
                                            </span>
                                            <span class="text-xs text-gray-500">{{ $payroll->user?->email }}</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $payroll->month }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $payroll->basic_salary, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $payroll->allowances, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $payroll->deductions, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $payroll->tax, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $payroll->net_salary, 2) }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$payroll->status === 'paid' ? 'green' : ($payroll->status === 'on_hold' ? 'yellow' : 'gray')">
                                            {{ ucfirst(str_replace('_', ' ', $payroll->status)) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        {{ $payroll->payment_date?->format('M d, Y') ?? '-' }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button
                                                size="sm"
                                                variant="subtle"
                                                icon="square-pen"
                                                x-on:click="$tsui.open.slide('edit-payroll'), $wire.dispatch('edit-payroll', { id: {{ $payroll->id }} })"
                                            />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            </flux:table.rows>
                        @endforeach
                    </flux:table>
                @else
                    <div class="p-6 text-center">
                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Payroll Records') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create the first payroll record to start tracking payments.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="allowances">
            <flux:card>
                @if($this->allowances->count())
                    <flux:table :paginate="$this->allowances">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Employee') }}</flux:table.column>
                            <flux:table.column>{{ __('Payroll Month') }}</flux:table.column>
                            <flux:table.column>{{ __('Type') }}</flux:table.column>
                            <flux:table.column>{{ __('Amount') }}</flux:table.column>
                            <flux:table.column>{{ __('Description') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->allowances as $allowance)
                            <flux:table.rows>
                                <flux:table.row :key="$allowance->id">
                                    <flux:table.cell>
                                        {{ $allowance->payroll?->user?->first_name }} {{ $allowance->payroll?->user?->last_name }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $allowance->payroll?->month }}</flux:table.cell>
                                    <flux:table.cell>{{ $allowance->type }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $allowance->amount, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ $allowance->description ?? '-' }}</flux:table.cell>
                                </flux:table.row>
                            </flux:table.rows>
                        @endforeach
                    </flux:table>
                @else
                    <div class="p-6 text-center">
                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Payroll Allowances') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Allowance entries will appear here once they are added.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="deductions">
            <flux:card>
                @if($this->deductions->count())
                    <flux:table :paginate="$this->deductions">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Employee') }}</flux:table.column>
                            <flux:table.column>{{ __('Payroll Month') }}</flux:table.column>
                            <flux:table.column>{{ __('Type') }}</flux:table.column>
                            <flux:table.column>{{ __('Amount') }}</flux:table.column>
                            <flux:table.column>{{ __('Description') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->deductions as $deduction)
                            <flux:table.rows>
                                <flux:table.row :key="$deduction->id">
                                    <flux:table.cell>
                                        {{ $deduction->payroll?->user?->first_name }} {{ $deduction->payroll?->user?->last_name }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $deduction->payroll?->month }}</flux:table.cell>
                                    <flux:table.cell>{{ $deduction->type }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format((float) $deduction->amount, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ $deduction->description ?? '-' }}</flux:table.cell>
                                </flux:table.row>
                            </flux:table.rows>
                        @endforeach
                    </flux:table>
                @else
                    <div class="p-6 text-center">
                        <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Payroll Deductions') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Deduction entries will appear here once they are added.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>
    </flux:tab.group>

    <x-slide id="create-payroll" title="{{ __('Create Payroll') }}" size="xl">
        <livewire:pages::accountant.payroll.create />
    </x-slide>

    <x-slide id="edit-payroll" title="{{ __('Edit Payroll') }}" size="xl">
        <livewire:pages::accountant.payroll.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
