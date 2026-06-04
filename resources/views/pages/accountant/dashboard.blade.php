<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Accountant;
use Illuminate\Support\Facades\Auth;

new
#[Title('Accountant Dashboard')]
#[Layout('layouts.accountant')]
class extends Component {

    #[Computed]
    public function accountant()
    {
        return Auth::user()->accountant;
    }

    #[Computed]
    public function totalInvoices(): int
    {
        return (int) FeeInvoice::count();
    }

    #[Computed]
    public function totalCollected(): float
    {
        return (float) FeePayment::sum('amount');
    }

    #[Computed]
    public function totalOutstanding(): float
    {
        return (float) FeeInvoice::whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    #[Computed]
    public function overdueInvoices(): int
    {
        return (int) FeeInvoice::where('status', 'overdue')
            ->count();
    }

    #[Computed]
    public function recentPayments()
    {
        return FeePayment::with('student')
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Accountant Dashboard') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Overview of institutional finances.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Invoices') }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $this->totalInvoices }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Collected') }}</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalCollected, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Outstanding') }}</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->totalOutstanding, 2) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Overdue Invoices') }}</p>
            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $this->overdueInvoices }}</p>
        </flux:card>
    </div>

    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Recent Payments') }}</flux:heading>
        @if($this->recentPayments->count())
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Amount') }}</flux:table.column>
                    <flux:table.column>{{ __('Method') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->recentPayments as $payment)
                        <flux:table.row>
                            <flux:table.cell>{{ $payment->student?->first_name }} {{ $payment->student?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($payment->amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</flux:table.cell>
                            <flux:table.cell>{{ $payment->payment_date?->format('M d, Y') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No payments recorded yet.') }}</flux:text>
        @endif
    </flux:card>
</div>
</div>
