<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Payroll;
use App\Models\PayrollAllowance;
use App\Models\PayrollDeduction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Create Payroll')] 
class extends Component {
    use Interactions;

    public string $user_id = '';
    public string $month = '';
    public string $basic_salary = '';
    public string $tax = '0';
    public string $payment_date = '';
    public string $status = 'pending';
    public string $remarks = '';
    public array $allowances = [];
    public array $deductions = [];

    public function mount(): void
    {
        $this->allowances = $this->blankLineItems();
        $this->deductions = $this->blankLineItems();
    }

    #[Computed]
    public function employees()
    {
        return User::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('role', ['teacher', 'staff'])
            ->with(['teacher', 'staff'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function updatedUserId(): void
    {
        $this->syncBasicSalaryFromEmployee();
    }

    public function addAllowanceRow(): void
    {
        $this->allowances[] = ['type' => '', 'amount' => '', 'description' => ''];
    }

    public function removeAllowanceRow(int $index): void
    {
        unset($this->allowances[$index]);
        $this->allowances = array_values($this->allowances) ?: $this->blankLineItems();
    }

    public function addDeductionRow(): void
    {
        $this->deductions[] = ['type' => '', 'amount' => '', 'description' => ''];
    }

    public function removeDeductionRow(int $index): void
    {
        unset($this->deductions[$index]);
        $this->deductions = array_values($this->deductions) ?: $this->blankLineItems();
    }

    public function allowancesTotal(): float
    {
        return array_reduce($this->normalizedLineItems($this->allowances), fn (float $carry, array $item): float => $carry + (float) $item['amount'], 0.0);
    }

    public function deductionsTotal(): float
    {
        return array_reduce($this->normalizedLineItems($this->deductions), fn (float $carry, array $item): float => $carry + (float) $item['amount'], 0.0);
    }

    public function netSalary(): float
    {
        return round((float) ($this->basic_salary ?: 0) + $this->allowancesTotal() - $this->deductionsTotal() - (float) ($this->tax ?: 0), 2);
    }

    public function save(): void
    {
        if ($this->basic_salary === '') {
            $this->syncBasicSalaryFromEmployee();
        }

        $validated = $this->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month' => [
                'required',
                'date_format:Y-m',
                Rule::unique('payrolls')->where(function ($query) {
                    $query->where('tenant_id', Auth::user()->tenant_id)
                        ->where('user_id', $this->user_id);
                }),
            ],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,paid,on_hold'],
            'remarks' => ['nullable', 'string'],
            'allowances' => ['array'],
            'allowances.*.type' => ['nullable', 'string', 'max:255'],
            'allowances.*.amount' => ['nullable', 'numeric', 'min:0'],
            'allowances.*.description' => ['nullable', 'string', 'max:255'],
            'deductions' => ['array'],
            'deductions.*.type' => ['nullable', 'string', 'max:255'],
            'deductions.*.amount' => ['nullable', 'numeric', 'min:0'],
            'deductions.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        $allowances = $this->normalizedLineItems($validated['allowances'] ?? []);
        $deductions = $this->normalizedLineItems($validated['deductions'] ?? []);
        $allowancesTotal = array_reduce($allowances, fn (float $carry, array $item): float => $carry + (float) $item['amount'], 0.0);
        $deductionsTotal = array_reduce($deductions, fn (float $carry, array $item): float => $carry + (float) $item['amount'], 0.0);
        $basicSalary = number_format((float) $validated['basic_salary'], 2, '.', '');
        $tax = number_format((float) ($validated['tax'] ?? 0), 2, '.', '');
        $netSalary = number_format(((float) $basicSalary + $allowancesTotal - $deductionsTotal - (float) $tax), 2, '.', '');

        DB::transaction(function () use ($validated, $basicSalary, $tax, $allowances, $deductions, $allowancesTotal, $deductionsTotal, $netSalary): void {
            $payroll = Payroll::create([
                'tenant_id' => Auth::user()->tenant_id,
                'uuid' => Str::uuid(),
                'user_id' => $validated['user_id'],
                'month' => $validated['month'],
                'basic_salary' => $basicSalary,
                'allowances' => number_format($allowancesTotal, 2, '.', ''),
                'deductions' => number_format($deductionsTotal, 2, '.', ''),
                'tax' => $tax,
                'net_salary' => $netSalary,
                'payment_date' => $validated['payment_date'] ?? null,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null,
                'processed_by' => Auth::id(),
            ]);

            foreach ($allowances as $allowance) {
                PayrollAllowance::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'uuid' => Str::uuid(),
                    'payroll_id' => $payroll->id,
                    'type' => $allowance['type'],
                    'amount' => number_format((float) $allowance['amount'], 2, '.', ''),
                    'description' => $allowance['description'] !== '' ? $allowance['description'] : null,
                ]);
            }

            foreach ($deductions as $deduction) {
                PayrollDeduction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'uuid' => Str::uuid(),
                    'payroll_id' => $payroll->id,
                    'type' => $deduction['type'],
                    'amount' => number_format((float) $deduction['amount'], 2, '.', ''),
                    'description' => $deduction['description'] !== '' ? $deduction['description'] : null,
                ]);
            }
        });

        Flux::toast(variant: 'success', text: __('Payroll created successfully.'));

        $this->redirect(route('payroll.index'), navigate: true);
    }

    private function blankLineItems(): array
    {
        return [['type' => '', 'amount' => '', 'description' => '']];
    }

    private function syncBasicSalaryFromEmployee(): void
    {
        $salary = $this->selectedEmployeeSalary();

        if ($salary !== null) {
            $this->basic_salary = (string) $salary;
        }
    }

    private function selectedEmployeeSalary(): ?string
    {
        if ($this->user_id === '') {
            return null;
        }

        $employee = User::where('tenant_id', Auth::user()->tenant_id)
            ->with(['teacher', 'staff'])
            ->find($this->user_id);

        if (! $employee) {
            return null;
        }

        return $employee->teacher?->salary ?? $employee->staff?->salary;
    }

    private function normalizedLineItems(array $items): array
    {
        return array_values(array_filter(array_map(function (array $item): array {
            return [
                'type' => trim((string) ($item['type'] ?? '')),
                'amount' => trim((string) ($item['amount'] ?? '')),
                'description' => trim((string) ($item['description'] ?? '')),
            ];
        }, $items), fn (array $item): bool => $item['type'] !== '' && $item['amount'] !== ''));
    }
};
?>

<div>
    <x-dialog />

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Employee') }}" variant="listbox" wire:model.live="user_id" searchable required>
                <flux:select.option value="">{{ __('Select Employee') }}</flux:select.option>
                @forelse($this->employees as $employee)
                    <flux:select.option value="{{ $employee->id }}">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                        @if($employee->teacher)
                            ({{ __('Teacher') }})
                        @elseif($employee->staff)
                            ({{ __('Staff') }})
                        @endif
                    </flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Employees Available') }}</flux:select.option>
                @endforelse
            </flux:select>
            <flux:input label="{{ __('Month') }}" type="month" wire:model="month" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Basic Salary') }}" type="text" inputmode="decimal" wire:model="basic_salary" required />
            <flux:input label="{{ __('Tax') }}" type="text" inputmode="decimal" wire:model="tax" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Payment Date') }}" wire:model="payment_date" />
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="on_hold">{{ __('On Hold') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:card>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Allowances') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Add one or more payroll allowances.') }}</p>
                </div>
                <flux:button type="button" class="button" size="sm" variant="subtle" icon="plus" wire:click="addAllowanceRow">
                    {{ __('Add Allowance') }}
                </flux:button>
            </div>

            <div class="mt-4 space-y-4">
                @foreach($allowances as $index => $allowance)
                    <div class="grid gap-4 xl:grid-cols-12">
                        <div class="xl:col-span-3">
                            <flux:input label="{{ __('Type') }}" wire:model="allowances.{{ $index }}.type" />
                        </div>
                        <div class="xl:col-span-3">
                            <flux:input label="{{ __('Amount') }}" type="text" inputmode="decimal" wire:model="allowances.{{ $index }}.amount" />
                        </div>
                        <div class="xl:col-span-5">
                            <flux:input label="{{ __('Description') }}" wire:model="allowances.{{ $index }}.description" />
                        </div>
                        <div class="flex items-end xl:col-span-1">
                            <flux:button
                                type="button"
                                size="sm"
                                variant="danger"
                                icon="trash"
                                class="w-full"
                                wire:click="removeAllowanceRow({{ $index }})"
                                :disabled="count($allowances) === 1"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Deductions') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Add one or more payroll deductions.') }}</p>
                </div>
                <flux:button type="button" class="button" size="sm" variant="subtle" icon="plus" wire:click="addDeductionRow">
                    {{ __('Add Deduction') }}
                </flux:button>
            </div>

            <div class="mt-4 space-y-4">
                @foreach($deductions as $index => $deduction)
                    <div class="grid gap-4 xl:grid-cols-12">
                        <div class="xl:col-span-3">
                            <flux:input label="{{ __('Type') }}" wire:model="deductions.{{ $index }}.type" />
                        </div>
                        <div class="xl:col-span-3">
                            <flux:input label="{{ __('Amount') }}" type="text" inputmode="decimal" wire:model="deductions.{{ $index }}.amount" />
                        </div>
                        <div class="xl:col-span-5">
                            <flux:input label="{{ __('Description') }}" wire:model="deductions.{{ $index }}.description" />
                        </div>
                        <div class="flex items-end xl:col-span-1">
                            <flux:button
                                type="button"
                                size="sm"
                                variant="danger"
                                icon="trash"
                                class="w-full"
                                wire:click="removeDeductionRow({{ $index }})"
                                :disabled="count($deductions) === 1"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <div class="grid gap-4 lg:grid-cols-3">
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Allowances Total') }}</p>
                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($this->allowancesTotal(), 2) }}</p>
            </flux:card>
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Deductions Total') }}</p>
                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($this->deductionsTotal(), 2) }}</p>
            </flux:card>
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Net Salary') }}</p>
                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($this->netSalary(), 2) }}</p>
            </flux:card>
        </div>

        <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="4" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" class="button" variant="primary">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-payroll')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>

