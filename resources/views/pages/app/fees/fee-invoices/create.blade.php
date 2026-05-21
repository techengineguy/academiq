<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeType;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Fee Invoice')]
class extends Component {

    public string $student_id = '';
    public string $invoice_date = '';
    public string $due_date = '';
    public string $discount_amount = '0';
    public string $late_fee = '0';
    public string $remarks = '';
    public array $items = [];

    public function mount(): void
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->items = $this->blankItems();
    }

    #[Computed]
    public function students()
    {
        return Student::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->with(['user', 'class'])
            ->orderBy('roll_number')
            ->get();
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
        $this->items[] = ['fee_type_id' => '', 'amount' => '', 'description' => ''];
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

    public function save(): void
    {
        $validated = $this->validate([
            'student_id' => ['required', 'exists:students,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_type_id' => ['required', 'exists:fee_types,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        $itemsTotal = array_reduce($validated['items'], fn (float $carry, array $item): float => $carry + (float) $item['amount'], 0.0);
        $discount = (float) ($validated['discount_amount'] ?? 0);
        $lateFee = (float) ($validated['late_fee'] ?? 0);
        $totalAmount = round($itemsTotal - $discount + $lateFee, 2);

        DB::transaction(function () use ($validated, $totalAmount, $discount, $lateFee): void {
            $invoice = FeeInvoice::create([
                'tenant_id' => Auth::user()->tenant_id,
                'uuid' => Str::uuid(),
                'student_id' => $validated['student_id'],
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'total_amount' => number_format($totalAmount, 2, '.', ''),
                'discount_amount' => number_format($discount, 2, '.', ''),
                'late_fee' => number_format($lateFee, 2, '.', ''),
                'paid_amount' => '0.00',
                'balance' => number_format($totalAmount, 2, '.', ''),
                'status' => 'pending',
                'remarks' => $validated['remarks'] !== '' ? $validated['remarks'] : null,
            ]);

            foreach ($validated['items'] as $item) {
                FeeInvoiceItem::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'uuid' => Str::uuid(),
                    'fee_invoice_id' => $invoice->id,
                    'fee_type_id' => $item['fee_type_id'],
                    'amount' => number_format((float) $item['amount'], 2, '.', ''),
                    'description' => $item['description'] !== '' ? $item['description'] : null,
                ]);
            }
        });

        Flux::toast(variant: 'success', text: __('Invoice created successfully.'));

        $this->redirect(route('fee-invoices.index'), navigate: true);
    }

    private function blankItems(): array
    {
        return [['fee_type_id' => '', 'amount' => '', 'description' => '']];
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Student') }}" variant="listbox" wire:model="student_id" required>
            <flux:select.option value="">{{ __('Select Student') }}</flux:select.option>
            @foreach($this->students as $student)
                <flux:select.option value="{{ $student->id }}">
                    {{ $student->user?->first_name }} {{ $student->user?->last_name }} ({{ $student->class?->name ?? '-' }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Invoice Date') }}" wire:model="invoice_date" required />
            <flux:date-picker label="{{ __('Due Date') }}" wire:model="due_date" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Discount') }}" type="text" inputmode="decimal" wire:model="discount_amount" />
            <flux:input label="{{ __('Late Fee') }}" type="text" inputmode="decimal" wire:model="late_fee" />
        </div>

        <flux:card>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Invoice Items') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Add fee items to this invoice.') }}</p>
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
                            <flux:input label="{{ __('Amount') }}" type="text" inputmode="decimal" wire:model="items.{{ $index }}.amount" />
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

        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Amount') }}</p>
            <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalAmount(), 2) }}</p>
        </flux:card>

        <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create Invoice') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-fee-invoice')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
