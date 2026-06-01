<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

new
#[Title('Financial Reports')]
#[Layout('layouts.accountant')]
class extends Component {

    public string $reportPeriod = 'this_month';

    #[Computed]
    public function tenantId(): string
    {
        return Auth::user()->tenant_id;
    }

    #[Computed]
    public function dateRange(): array
    {
        return match ($this->reportPeriod) {
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    #[Computed]
    public function totalFeeCollected(): float
    {
        [$from, $to] = $this->dateRange;

        return (float) FeePayment::where('tenant_id', $this->tenantId)
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount');
    }

    #[Computed]
    public function totalOutstanding(): float
    {
        return (float) FeeInvoice::where('tenant_id', $this->tenantId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    #[Computed]
    public function totalOverdue(): float
    {
        return (float) FeeInvoice::where('tenant_id', $this->tenantId)
            ->where('status', 'overdue')
            ->sum('balance');
    }

    #[Computed]
    public function totalPayrollCost(): float
    {
        [$from, $to] = $this->dateRange;

        return (float) Payroll::where('tenant_id', $this->tenantId)
            ->whereBetween('payment_date', [$from, $to])
            ->sum('net_salary');
    }

    #[Computed]
    public function collectionByMethod(): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->dateRange;

        return FeePayment::where('tenant_id', $this->tenantId)
            ->whereBetween('payment_date', [$from, $to])
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Financial Reports') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Summary of institutional financial performance.') }}</p>
        </div>
        <flux:select wire:model.live="reportPeriod" class="w-48">
            <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="last_month">{{ __('Last Month') }}</flux:select.option>
            <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
            <flux:select.option value="last_year">{{ __('Last Year') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Fees Collected') }}</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalFeeCollected, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Outstanding Balance') }}</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->totalOutstanding, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Overdue Amount') }}</p>
            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->totalOverdue, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Payroll Expenses') }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalPayrollCost, 2) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Fee Collection by Payment Method') }}</flux:heading>
        @if($this->collectionByMethod->count())
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Payment Method') }}</flux:table.column>
                    <flux:table.column>{{ __('Total Collected') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->collectionByMethod as $item)
                        <flux:table.row>
                            <flux:table.cell>{{ ucfirst(str_replace('_', ' ', $item->payment_method)) }}</flux:table.cell>
                            <flux:table.cell class="font-medium">{{ number_format($item->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No payment data for the selected period.') }}</flux:text>
        @endif
    </flux:card>
</div>
</div>
