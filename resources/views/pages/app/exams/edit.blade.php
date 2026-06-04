<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Exam;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Exam')]
class extends Component {

    public ?Exam $exam = null;

    public string $name = '';
    public string $type = 'mid_term';
    public string $academic_year_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $description = '';
    public string $status = 'scheduled';
    public bool $result_published = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadExam($id);
        }
    }

    #[On('edit-exam')]
    public function loadExam(int $id): void
    {
        $this->exam = Exam::findOrFail($id);

        $this->name = $this->exam->name;
        $this->type = $this->exam->type;
        $this->academic_year_id = (string) $this->exam->academic_year_id;
        $this->start_date = $this->exam->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $this->exam->end_date?->format('Y-m-d') ?? '';
        $this->description = (string) ($this->exam->description ?? '');
        $this->status = $this->exam->status;
        $this->result_published = $this->exam->result_published;
    }

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderByDesc('start_date')
            ->get();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:mid_term,final,unit_test,practical,assignment'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:scheduled,ongoing,completed,cancelled'],
            'result_published' => ['boolean'],
        ]);

        $this->exam->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'academic_year_id' => $validated['academic_year_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'status' => $validated['status'],
            'result_published' => $validated['result_published'],
        ]);

        Flux::toast(variant: 'success', text: __('Exam updated successfully.'));

        $this->redirect(route('exams.index'), navigate: true);
    }
};
?>
<div>
    @if($this->exam)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Exam Name') }}" wire:model="name" required />
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

            <flux:checkbox label="{{ __('Result Published') }}" wire:model="result_published" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-exam')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
