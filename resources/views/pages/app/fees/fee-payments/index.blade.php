<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Fee Payments')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterMethod = '';

    public ?int $paymentIdToDelete = null;

    #[Computed]
    public function payments()
    {
        $query = FeePayment::with(['feeInvoice', 'student.user', 'receivedBy'])
            ->orderByDesc('payment_date');

        // Students can only see their own payments
        if (Auth::user()->role === 'student') {
            $query->where('student_id', Auth::user()->student?->id);
        }

        if ($this->filterMethod !== '') {
            $query->where('payment_method', $this->filterMethod);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function totalPayments(): int
    {
        return (int) FeePayment::count();
    }

    #[Computed]
    public function totalAmount(): float
    {
        return (float) FeePayment::sum('amount');
    }

    public function updatedFilterMethod(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterMethod = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->paymentIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this payment record?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->paymentIdToDelete) {
            return;
        }

        $payment = FeePayment::findOrFail($this->paymentIdToDelete);

        $invoice = $payment->feeInvoice;
        $payment->delete();

        if ($invoice) {
            $newPaid = (float) $invoice->payments()->sum('amount');
            $balance = (float) $invoice->total_amount - $newPaid;
            $status = $newPaid <= 0 ? 'pending' : ($balance <= 0 ? 'paid' : 'partial');
            $invoice->update([
                'paid_amount' => number_format($newPaid, 2, '.', ''),
                'balance' => number_format(max($balance, 0), 2, '.', ''),
                'status' => $status,
            ]);
        }

        $this->paymentIdToDelete = null;
        unset($this->payments);

        Flux::toast(variant: 'success', text: __('Payment deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Fee Payments') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Record and track fee payments from students.') }}</p>
        </div>

        @hasPermission('record-payments')
        <flux:button class="button" x-on:click="$tsui.open.slide('create-fee-payment')" icon="plus">
            {{ __('Record Payment') }}
        </flux:button>
        @endhasPermission
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Payments') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalPayments) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Collected') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalAmount, 2) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterMethod" placeholder="{{ __('All Methods') }}">
                <flux:select.option value="">{{ __('All Methods') }}</flux:select.option>
                <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                <flux:select.option value="card">{{ __('Card') }}</flux:select.option>
                <flux:select.option value="online">{{ __('Online') }}</flux:select.option>
                <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
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
                    <flux:table.column>{{ __('Received By') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->payments as $payment)
                    <flux:table.rows>
                        <flux:table.row :key="$payment->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $payment->receipt_number }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $payment->student?->user?->first_name }} {{ $payment->student?->user?->last_name }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $payment->feeInvoice?->invoice_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $payment->amount, 2) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $payment->payment_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $payment->receivedBy?->first_name }} {{ $payment->receivedBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $payment->id }})" />
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Payments') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Record a payment to start tracking collections.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-fee-payment" title="{{ __('Record Payment') }}" size="xl">
        <livewire:pages::app.fees.fee-payments.create />
    </x-slide>
</div>
