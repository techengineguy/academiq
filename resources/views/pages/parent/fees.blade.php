<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Concerns\ScopesToParentChildren;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Auth;

new
#[Title('Children Fees')]
#[Layout('layouts.parent')]
class extends Component {
    use WithPagination, ScopesToParentChildren;

    public string $filterChild = '';
    public string $tab = 'invoices';

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

    #[Computed]
    public function invoices()
    {
        $childIds = $this->parentChildIds();
        if ($this->filterChild !== '') {
            $childIds = [$this->filterChild];
        }

        return FeeInvoice::whereIn('student_id', $childIds)
            ->with(['student.user'])
            ->orderByDesc('invoice_date')
            ->paginate(10, ['*'], 'invoicesPage');
    }

    #[Computed]
    public function payments()
    {
        $childIds = $this->parentChildIds();
        if ($this->filterChild !== '') {
            $childIds = [$this->filterChild];
        }

        return FeePayment::whereIn('student_id', $childIds)
            ->with(['student.user', 'feeInvoice'])
            ->orderByDesc('payment_date')
            ->paginate(10, ['*'], 'paymentsPage');
    }

    #[Computed]
    public function totals(): array
    {
        $childIds = $this->parentChildIds();
        if ($this->filterChild !== '') {
            $childIds = [$this->filterChild];
        }

        return [
            'total_paid' => (float) FeePayment::whereIn('student_id', $childIds)->sum('amount'),
            'total_balance' => (float) FeeInvoice::whereIn('student_id', $childIds)
                ->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance'),
        ];
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Children Fees') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View invoices and payment history.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Paid') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totals['total_paid'], 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Outstanding Balance') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->totals['total_balance'], 2) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <flux:button :variant="$tab === 'invoices' ? 'primary' : 'subtle'" wire:click="selectTab('invoices')" :class="$tab === 'invoices' ? 'button' : ''" icon="receipt-percent">
                    {{ __('Invoices') }}
                </flux:button>
                <flux:button :variant="$tab === 'payments' ? 'primary' : 'subtle'" wire:click="selectTab('payments')" :class="$tab === 'payments' ? 'button' : ''" icon="wallet">
                    {{ __('Payment History') }}
                </flux:button>
            </div>

            <flux:select variant="listbox" wire:model.live="filterChild" placeholder="{{ __('All Children') }}" class="max-w-xs">
                <flux:select.option value="">{{ __('All Children') }}</flux:select.option>
                @foreach($this->children as $child)
                    <flux:select.option value="{{ $child->id }}">
                        {{ $child->user?->first_name }} {{ $child->user?->last_name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        @if($tab === 'invoices')
            @if($this->invoices->count())
                <flux:table :paginate="$this->invoices">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Invoice #') }}</flux:table.column>
                        <flux:table.column>{{ __('Child') }}</flux:table.column>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Total') }}</flux:table.column>
                        <flux:table.column>{{ __('Paid') }}</flux:table.column>
                        <flux:table.column>{{ __('Balance') }}</flux:table.column>
                        <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach($this->invoices as $invoice)
                        <flux:table.rows>
                            <flux:table.row :key="$invoice->id">
                                <flux:table.cell>{{ $invoice->invoice_number }}</flux:table.cell>
                                <flux:table.cell>{{ $invoice->student?->user?->first_name }} {{ $invoice->student?->user?->last_name }}</flux:table.cell>
                                <flux:table.cell>{{ $invoice->invoice_date?->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $invoice->total_amount, 2) }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $invoice->paid_amount, 2) }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $invoice->balance, 2) }}</flux:table.cell>
                                <flux:table.cell>{{ $invoice->due_date?->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $color = match($invoice->status) {
                                            'paid' => 'green', 'pending' => 'yellow', 'partial' => 'orange',
                                            'overdue' => 'red', 'cancelled' => 'gray', default => 'gray',
                                        };
                                    @endphp
                                    <flux:badge :color="$color">{{ ucfirst($invoice->status) }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        </flux:table.rows>
                    @endforeach
                </flux:table>
            @else
                <div class="p-6 text-center">
                    <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Invoices') }}</h3>
                </div>
            @endif
        @else
            @if($this->payments->count())
                <flux:table :paginate="$this->payments">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Receipt #') }}</flux:table.column>
                        <flux:table.column>{{ __('Child') }}</flux:table.column>
                        <flux:table.column>{{ __('Invoice') }}</flux:table.column>
                        <flux:table.column>{{ __('Amount') }}</flux:table.column>
                        <flux:table.column>{{ __('Method') }}</flux:table.column>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach($this->payments as $payment)
                        <flux:table.rows>
                            <flux:table.row :key="$payment->id">
                                <flux:table.cell>{{ $payment->receipt_number }}</flux:table.cell>
                                <flux:table.cell>{{ $payment->student?->user?->first_name }} {{ $payment->student?->user?->last_name }}</flux:table.cell>
                                <flux:table.cell>{{ $payment->feeInvoice?->invoice_number ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ number_format((float) $payment->amount, 2) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="blue">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $payment->payment_date?->format('M d, Y') }}</flux:table.cell>
                            </flux:table.row>
                        </flux:table.rows>
                    @endforeach
                </flux:table>
            @else
                <div class="p-6 text-center">
                    <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Payments') }}</h3>
                </div>
            @endif
        @endif
    </flux:card>
</div>
</div>
