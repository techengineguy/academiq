<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Fee Structure')]
class extends Component {

    public string $fee_type_id = '';
    public string $class_id = '';
    public string $academic_year_id = '';
    public string $amount = '';
    public string $frequency = 'annually';
    public string $due_date = '';

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

    public function save(): void
    {
        $validated = $this->validate([
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:one_time,monthly,quarterly,annually'],
            'due_date' => ['nullable', 'date'],
        ]);

        FeeStructure::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'fee_type_id' => $validated['fee_type_id'],
            'class_id' => $validated['class_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
            'due_date' => $validated['due_date'] !== '' ? $validated['due_date'] : null,
        ]);

        Flux::toast(variant: 'success', text: __('Fee structure created successfully.'));

        $this->redirect(route('fee-structures.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
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
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-fee-structure')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
