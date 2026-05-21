<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\FeePayment;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Record Fee Payment')]
class extends Component {

    public string $fee_invoice_id = '';
    public string $amount = '';
    public string $payment_date = '';
    public string $payment_method = 'cash';
    public string $transaction_id = '';
    public string $reference_number = '';
    public string $remarks = '';

    public function mount(): void
    {
        $this->payment_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function invoices()
    {
        return FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with(['student.user'])
            ->orderByDesc('invoice_date')
            ->get();
    }

    public function updatedFeeInvoiceId(): void
    {
        if ($this->fee_invoice_id !== '') {
            $invoice = FeeInvoice::find($this->fee_invoice_id);
            if ($invoice) {
                $this->amount = (string) $invoice->balance;
            }
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'fee_invoice_id' => ['required', 'exists:fee_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,cheque,card,online,bank_transfer'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $invoice = FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($validated['fee_invoice_id']);

        FeePayment::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'fee_invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'receipt_number' => 'RCP-' . strtoupper(Str::random(8)),
            'amount' => number_format((float) $validated['amount'], 2, '.', ''),
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] !== '' ? $validated['transaction_id'] : null,
            'reference_number' => $validated['reference_number'] !== '' ? $validated['reference_number'] : null,
            'received_by' => Auth::id(),
            'remarks' => $validated['remarks'] !== '' ? $validated['remarks'] : null,
        ]);

        $newPaid = (float) $invoice->payments()->sum('amount');
        $balance = (float) $invoice->total_amount - $newPaid;
        $status = $balance <= 0 ? 'paid' : 'partial';

        $invoice->update([
            'paid_amount' => number_format($newPaid, 2, '.', ''),
            'balance' => number_format(max($balance, 0), 2, '.', ''),
            'status' => $status,
        ]);

        Flux::toast(variant: 'success', text: __('Payment recorded successfully.'));

        $this->redirect(route('fee-payments.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Invoice') }}" variant="listbox" wire:model.live="fee_invoice_id" required>
            <flux:select.option value="">{{ __('Select Invoice') }}</flux:select.option>
            @foreach($this->invoices as $invoice)
                <flux:select.option value="{{ $invoice->id }}">
                    {{ $invoice->invoice_number }} - {{ $invoice->student?->user?->first_name }} {{ $invoice->student?->user?->last_name }}
                    ({{ __('Balance') }}: {{ number_format((float) $invoice->balance, 2) }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Amount') }}" type="text" inputmode="decimal" wire:model="amount" required />
            <flux:date-picker label="{{ __('Payment Date') }}" wire:model="payment_date" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Payment Method') }}" variant="listbox" wire:model="payment_method" required>
                <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                <flux:select.option value="card">{{ __('Card') }}</flux:select.option>
                <flux:select.option value="online">{{ __('Online') }}</flux:select.option>
                <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
            </flux:select>
            <flux:input label="{{ __('Transaction ID') }}" wire:model="transaction_id" placeholder="{{ __('Optional') }}" />
        </div>

        <flux:input label="{{ __('Reference Number') }}" wire:model="reference_number" placeholder="{{ __('Optional') }}" />

        <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Record Payment') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-fee-payment')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
