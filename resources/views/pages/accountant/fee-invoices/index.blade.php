<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new
#[Title('Fee Invoices')]
#[Layout('layouts.accountant')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';

    public ?int $invoiceIdToDelete = null;

    #[Computed]
    public function invoices()
    {
        $query = FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->with(['student.user', 'student.class'])
            ->orderByDesc('invoice_date');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function totalInvoices(): int
    {
        return (int) FeeInvoice::where('tenant_id', Auth::user()->tenant_id)->count();
    }

    #[Computed]
    public function totalPending(): float
    {
        return (float) FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    #[Computed]
    public function totalCollected(): float
    {
        return (float) FeeInvoice::where('tenant_id', Auth::user()->tenant_id)->sum('paid_amount');
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->invoiceIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this invoice?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->invoiceIdToDelete) {
            return;
        }

        FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->invoiceIdToDelete)
            ->delete();

        $this->invoiceIdToDelete = null;
        unset($this->invoices);

        Flux::toast(variant: 'success', text: __('Invoice deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Fee Invoices') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Generate and manage student fee invoices.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-fee-invoice')" icon="plus">
            {{ __('New Invoice') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Invoices') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalInvoices) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Collected') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalCollected, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Outstanding Balance') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->totalPending, 2) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->invoices->count())
            <flux:table :paginate="$this->invoices">
                <flux:table.columns>
                    <flux:table.column>{{ __('Invoice #') }}</flux:table.column>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Total') }}</flux:table.column>
                    <flux:table.column>{{ __('Paid') }}</flux:table.column>
                    <flux:table.column>{{ __('Balance') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->invoices as $invoice)
                    <flux:table.rows>
                        <flux:table.row :key="$invoice->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span>{{ $invoice->student?->user?->first_name }} {{ $invoice->student?->user?->last_name }}</span>
                                    <span class="text-xs text-gray-500">{{ $invoice->student?->admission_number ?? '-' }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $invoice->student?->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $invoice->total_amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $invoice->paid_amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $invoice->balance, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ $invoice->due_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColor = match($invoice->status) {
                                        'pending' => 'yellow',
                                        'partial' => 'orange',
                                        'paid' => 'green',
                                        'overdue' => 'red',
                                        'cancelled' => 'gray',
                                        default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$statusColor">
                                    {{ ucfirst($invoice->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-fee-invoice'), $wire.dispatch('edit-fee-invoice', { id: {{ $invoice->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $invoice->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Invoices') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create an invoice to start billing students.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-fee-invoice" title="{{ __('Create Invoice') }}" size="xl">
        <livewire:pages::accountant.fee-invoices.create />
    </x-slide>

    <x-slide id="edit-fee-invoice" title="{{ __('Edit Invoice') }}" size="xl">
        <livewire:pages::accountant.fee-invoices.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
