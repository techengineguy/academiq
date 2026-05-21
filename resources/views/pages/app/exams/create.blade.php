<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Exam;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Exam')]
class extends Component {

    public string $name = '';
    public string $type = 'mid_term';
    public string $academic_year_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $description = '';
    public string $status = 'scheduled';

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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:mid_term,final,unit_test,practical,assignment'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:scheduled,ongoing,completed,cancelled'],
        ]);

        Exam::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'status' => $validated['status'],
            'result_published' => false,
        ]);

        Flux::toast(variant: 'success', text: __('Exam created successfully.'));

        $this->redirect(route('exams.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Exam Name') }}" wire:model="name" placeholder="{{ __('e.g., Mid Term Examination 2026') }}" required />
            <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
                <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                @foreach($this->academicYears as $year)
                    <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
                <flux:select.option value="mid_term">{{ __('Mid Term') }}</flux:select.option>
                <flux:select.option value="final">{{ __('Final') }}</flux:select.option>
                <flux:select.option value="unit_test">{{ __('Unit Test') }}</flux:select.option>
                <flux:select.option value="practical">{{ __('Practical') }}</flux:select.option>
                <flux:select.option value="assignment">{{ __('Assignment') }}</flux:select.option>
            </flux:select>
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="scheduled">{{ __('Scheduled') }}</flux:select.option>
                <flux:select.option value="ongoing">{{ __('Ongoing') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Start Date') }}" wire:model="start_date" required />
            <flux:date-picker label="{{ __('End Date') }}" wire:model="end_date" required />
        </div>

        <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-exam')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
