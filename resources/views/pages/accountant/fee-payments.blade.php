<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Auth;

new
#[Title('Fee Payments')]
#[Layout('layouts.accountant')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filterMethod = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMethod(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function payments()
    {
        return FeePayment::where('tenant_id', Auth::user()->tenant_id)
            ->with('student', 'invoice')
            ->when($this->filterMethod, fn ($q) => $q->where('payment_method', $this->filterMethod))
            ->when($this->search, function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('payment_date')
            ->paginate(15);
    }

    #[Computed]
    public function totalPayments(): float
    {
        return (float) FeePayment::where('tenant_id', Auth::user()->tenant_id)
            ->when($this->filterMethod, fn ($q) => $q->where('payment_method', $this->filterMethod))
            ->sum('amount');
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Fee Payments') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track all fee payment transactions.') }}</p>
    </div>

    <flux:card>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Payments Collected') }}</p>
        <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalPayments, 2) }}</p>
    </flux:card>

    <flux:card>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center mb-4">
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by student name...')" icon="magnifying-glass" class="sm:w-64" />
            <flux:select wire:model.live="filterMethod" :placeholder="__('All Methods')" class="sm:w-48">
                <flux:select.option value="">{{ __('All Methods') }}</flux:select.option>
                <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                <flux:select.option value="online">{{ __('Online') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->payments->count())
            <flux:table :paginate="$this->payments">
                <flux:table.columns>
                    <flux:table.column>{{ __('Receipt #') }}</flux:table.column>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Invoice #') }}</flux:table.column>
                    <flux:table.column>{{ __('Amount') }}</flux:table.column>
                    <flux:table.column>{{ __('Method') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->payments as $payment)
                        <flux:table.row wire:key="payment-{{ $payment->id }}">
                            <flux:table.cell>{{ $payment->receipt_number }}</flux:table.cell>
                            <flux:table.cell>{{ $payment->student?->first_name }} {{ $payment->student?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $payment->invoice?->invoice_number }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-green-600 dark:text-green-400">{{ number_format($payment->amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</flux:table.cell>
                            <flux:table.cell>{{ $payment->payment_date?->format('M d, Y') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No payments found.') }}</flux:text>
        @endif
    </flux:card>
</div>
</div>
