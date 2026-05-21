<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Edit Fee Invoice')]
class extends Component {

    public ?FeeInvoice $invoice = null;

    public string $due_date = '';
    public string $discount_amount = '';
    public string $late_fee = '';
    public string $status = 'pending';
    public string $remarks = '';
    public array $items = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadInvoice($id);
        }
    }

    #[On('edit-fee-invoice')]
    public function loadInvoice(int $id): void
    {
        $this->invoice = FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->with(['student.user', 'items.feeType'])
            ->findOrFail($id);

        $this->due_date = $this->invoice->due_date?->format('Y-m-d') ?? '';
        $this->discount_amount = (string) $this->invoice->discount_amount;
        $this->late_fee = (string) $this->invoice->late_fee;
        $this->status = $this->invoice->status;
        $this->remarks = (string) ($this->invoice->remarks ?? '');

        $this->items = $this->invoice->items->map(fn (FeeInvoiceItem $item): array => [
            'id' => $item->id,
            'fee_type_id' => (string) $item->fee_type_id,
            'amount' => (string) $item->amount,
            'description' => (string) ($item->description ?? ''),
        ])->values()->all() ?: $this->blankItems();
    }

    #[Computed]
    public function feeTypes()
    {
        return FeeType::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function addItem(): void
    {
        $this->items[] = ['id' => null, 'fee_type_id' => '', 'amount' => '', 'description' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items) ?: $this->blankItems();
    }

    public function totalAmount(): float
    {
        $itemsTotal = array_reduce($this->items, fn (float $carry, array $item): float => $carry + (float) ($item['amount'] ?: 0), 0.0);

        return round($itemsTotal - (float) ($this->discount_amount ?: 0) + (float) ($this->late_fee ?: 0), 2);
    }

    public function update(): void
    {
        $validated = $this->validate([
            'due_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,partial,paid,overdue,cancelled'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_type_id' => ['required', 'exists:fee_types,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        $discount = (float) ($validated['discount_amount'] ?? 0);
        $lateFee = (float) ($validated['late_fee'] ?? 0);
        $itemsTotal = array_reduce($validated['items'], fn (float $carry, array $item): float => $carry + (float) $item['amount'], 0.0);
        $totalAmount = round($itemsTotal - $discount + $lateFee, 2);
        $balance = round($totalAmount - (float) $this->invoice->paid_amount, 2);

        $this->invoice->update([
            'due_date' => $validated['due_date'],
            'discount_amount' => number_format($discount, 2, '.', ''),
            'late_fee' => number_format($lateFee, 2, '.', ''),
            'total_amount' => number_format($totalAmount, 2, '.', ''),
            'balance' => number_format(max($balance, 0), 2, '.', ''),
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] !== '' ? $validated['remarks'] : null,
        ]);

        $this->invoice->items()->delete();

        foreach ($validated['items'] as $item) {
            FeeInvoiceItem::create([
                'tenant_id' => Auth::user()->tenant_id,
                'uuid' => Str::uuid(),
                'fee_invoice_id' => $this->invoice->id,
                'fee_type_id' => $item['fee_type_id'],
                'amount' => number_format((float) $item['amount'], 2, '.', ''),
                'description' => $item['description'] !== '' ? $item['description'] : null,
            ]);
        }

        Flux::toast(variant: 'success', text: __('Invoice updated successfully.'));

        $this->redirect(route('fee-invoices.index'), navigate: true);
    }

    private function blankItems(): array
    {
        return [['id' => null, 'fee_type_id' => '', 'amount' => '', 'description' => '']];
    }
};
?>
<div>
    @if($this->invoice)
        <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-zinc-800">
            <div class="grid gap-2 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Invoice') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $this->invoice->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Student') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $this->invoice->student?->user?->first_name }} {{ $this->invoice->student?->user?->last_name }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Paid') }}</p>
                    <p class="text-gray-900 dark:text-white">{{ number_format((float) $this->invoice->paid_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('New Total') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($this->totalAmount(), 2) }}</p>
                </div>
            </div>
        </div>

        <form wire:submit="update" class="space-y-6">
            <flux:date-picker label="{{ __('Due Date') }}" wire:model="due_date" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Discount') }}" type="text" inputmode="decimal" wire:model.live="discount_amount" />
                <flux:input label="{{ __('Late Fee') }}" type="text" inputmode="decimal" wire:model.live="late_fee" />
            </div>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>

            <flux:card>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Invoice Items') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Update fee items for this invoice.') }}</p>
                    </div>
                    <flux:button type="button" class="button" size="sm" variant="subtle" icon="plus" wire:click="addItem">
                        {{ __('Add Item') }}
                    </flux:button>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach($items as $index => $item)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-zinc-700 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <flux:select label="{{ __('Fee Type') }}" variant="listbox" wire:model="items.{{ $index }}.fee_type_id">
                                    <flux:select.option value="">{{ __('Select') }}</flux:select.option>
                                    @foreach($this->feeTypes as $feeType)
                                        <flux:select.option value="{{ $feeType->id }}">{{ $feeType->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:input label="{{ __('Amount') }}" type="text" inputmode="decimal" wire:model.live="items.{{ $index }}.amount" />
                            </div>
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <flux:input label="{{ __('Description') }}" wire:model="items.{{ $index }}.description" placeholder="{{ __('Optional') }}" />
                                </div>
                                <flux:button
                                    type="button"
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="removeItem({{ $index }})"
                                    :disabled="count($items) === 1"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-fee-invoice')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
