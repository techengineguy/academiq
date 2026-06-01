<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new
#[Title('Fee Invoices')]
#[Layout('layouts.accountant')]
class extends Component {
    use WithPagination;

    public string $filterStatus = '';
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function invoices()
    {
        return FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->with('student')
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('invoice_date')
            ->paginate(15);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Fee Invoices') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage all student fee invoices.') }}</p>
        </div>
    </div>

    <flux:card>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center mb-4">
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by student name...')" icon="magnifying-glass" class="sm:w-64" />
            <flux:select wire:model.live="filterStatus" :placeholder="__('All Statuses')" class="sm:w-48">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->invoices->count())
            <flux:table :paginate="$this->invoices">
                <flux:table.columns>
                    <flux:table.column>{{ __('Invoice #') }}</flux:table.column>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Total') }}</flux:table.column>
                    <flux:table.column>{{ __('Paid') }}</flux:table.column>
                    <flux:table.column>{{ __('Balance') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->invoices as $invoice)
                        <flux:table.row wire:key="invoice-{{ $invoice->id }}">
                            <flux:table.cell>{{ $invoice->invoice_number }}</flux:table.cell>
                            <flux:table.cell>{{ $invoice->student?->first_name }} {{ $invoice->student?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $invoice->invoice_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($invoice->total_amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($invoice->paid_amount, 2) }}</flux:table.cell>
                            <flux:table.cell class="font-medium {{ $invoice->balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ number_format($invoice->balance, 2) }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $invoice->due_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge
                                    :color="match($invoice->status) {
                                        'paid' => 'green',
                                        'partial' => 'yellow',
                                        'overdue' => 'red',
                                        default => 'zinc'
                                    }"
                                    size="sm"
                                >
                                    {{ ucfirst($invoice->status) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No invoices found.') }}</flux:text>
        @endif
    </flux:card>
</div>
</div>
