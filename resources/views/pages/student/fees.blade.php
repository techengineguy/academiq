<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Fees')]
#[Layout('layouts.student')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function invoices()
    {
        $student = Auth::user()->student;
        if (! $student) {
            return FeeInvoice::where('id', 0)->paginate(10);
        }

        return FeeInvoice::where('student_id', $student->id)
            ->with('items.feeType')
            ->orderByDesc('invoice_date')
            ->paginate(10);
    }

    #[Computed]
    public function totalPaid(): float
    {
        $student = Auth::user()->student;

        return $student ? (float) FeePayment::where('student_id', $student->id)->sum('amount') : 0;
    }

    #[Computed]
    public function totalBalance(): float
    {
        $student = Auth::user()->student;

        return $student ? (float) FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance') : 0;
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Fees') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View your fee invoices and payment history.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Paid') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalPaid, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Outstanding Balance') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->totalBalance, 2) }}</p>
        </flux:card>
    </div>

    <flux:card>
        @if($this->invoices->count())
            <flux:table :paginate="$this->invoices">
                <flux:table.columns>
                    <flux:table.column>{{ __('Invoice #') }}</flux:table.column>
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
    </flux:card>
</div>
</div>
