<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Fee Structure')]
class extends Component {

    public ?FeeStructure $structure = null;

    public string $fee_type_id = '';
    public string $class_id = '';
    public string $academic_year_id = '';
    public string $amount = '';
    public string $frequency = 'annually';
    public string $due_date = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadStructure($id);
        }
    }

    #[On('edit-fee-structure')]
    public function loadStructure(int $id): void
    {
        $this->structure = FeeStructure::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $this->fee_type_id = (string) $this->structure->fee_type_id;
        $this->class_id = (string) $this->structure->class_id;
        $this->academic_year_id = (string) $this->structure->academic_year_id;
        $this->amount = (string) $this->structure->amount;
        $this->frequency = $this->structure->frequency;
        $this->due_date = $this->structure->due_date?->format('Y-m-d') ?? '';
    }

    #[Computed]
    public function feeTypes()
    {
        return FeeType::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function classes()
    {
        return ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->with('sections')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('start_date')
            ->get();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:one_time,monthly,quarterly,annually'],
            'due_date' => ['nullable', 'date'],
        ]);

        $this->structure->update([
            'fee_type_id' => $validated['fee_type_id'],
            'class_id' => $validated['class_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
            'due_date' => $validated['due_date'] !== '' ? $validated['due_date'] : null,
        ]);

        Flux::toast(variant: 'success', text: __('Fee structure updated successfully.'));

        $this->redirect(route('fee-structures.index'), navigate: true);
    }
};
?>
<div>
    @if($this->structure)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Fee Type') }}" variant="listbox" wire:model="fee_type_id" required>
                    <flux:select.option value="">{{ __('Select Fee Type') }}</flux:select.option>
                    @foreach($this->feeTypes as $feeType)
                        <flux:select.option value="{{ $feeType->id }}">{{ $feeType->name }} ({{ $feeType->code }})</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id" required>
                    <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                    @foreach($this->classes as $class)
                        <flux:select.option value="{{ $class->id }}">
                            {{ $class->name }}@if($class->sections->count()) ({{ $class->sections->pluck('name')->join(', ') }})@endif
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
                    <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                    @foreach($this->academicYears as $year)
                        <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="{{ __('Frequency') }}" variant="listbox" wire:model="frequency" required>
                    <flux:select.option value="one_time">{{ __('One Time') }}</flux:select.option>
                    <flux:select.option value="monthly">{{ __('Monthly') }}</flux:select.option>
                    <flux:select.option value="quarterly">{{ __('Quarterly') }}</flux:select.option>
                    <flux:select.option value="annually">{{ __('Annually') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Amount') }}" type="text" inputmode="decimal" wire:model="amount" required />
                <flux:date-picker label="{{ __('Due Date') }}" wire:model="due_date" />
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-fee-structure')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
