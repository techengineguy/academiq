<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

new
#[Title('Payroll')]
#[Layout('layouts.accountant')]
class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function payrolls()
    {
        return Payroll::where('tenant_id', Auth::user()->tenant_id)
            ->with(['user', 'processedBy'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($uq) {
                    $uq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('payment_date')
            ->paginate(15);
    }

    #[Computed]
    public function totalNetSalary(): float
    {
        return (float) Payroll::where('tenant_id', Auth::user()->tenant_id)->sum('net_salary');
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Payroll') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View staff payroll records.') }}</p>
    </div>

    <flux:card>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Net Salary Paid') }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalNetSalary, 2) }}</p>
    </flux:card>

    <flux:card>
        <div class="mb-4">
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by staff name...')" icon="magnifying-glass" class="sm:w-64" />
        </div>

        @if($this->payrolls->count())
            <flux:table :paginate="$this->payrolls">
                <flux:table.columns>
                    <flux:table.column>{{ __('Staff Member') }}</flux:table.column>
                    <flux:table.column>{{ __('Basic Salary') }}</flux:table.column>
                    <flux:table.column>{{ __('Allowances') }}</flux:table.column>
                    <flux:table.column>{{ __('Deductions') }}</flux:table.column>
                    <flux:table.column>{{ __('Net Salary') }}</flux:table.column>
                    <flux:table.column>{{ __('Payment Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->payrolls as $payroll)
                        <flux:table.row wire:key="payroll-{{ $payroll->id }}">
                            <flux:table.cell>{{ $payroll->user?->first_name }} {{ $payroll->user?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($payroll->basic_salary, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-green-600 dark:text-green-400">+ {{ number_format($payroll->total_allowances ?? 0, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-red-600 dark:text-red-400">- {{ number_format($payroll->total_deductions ?? 0, 2) }}</flux:table.cell>
                            <flux:table.cell class="font-bold">{{ number_format($payroll->net_salary, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ $payroll->payment_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge
                                    :color="$payroll->status === 'paid' ? 'green' : 'zinc'"
                                    size="sm"
                                >
                                    {{ ucfirst($payroll->status) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No payroll records found.') }}</flux:text>
        @endif
    </flux:card>
</div>
</div>
