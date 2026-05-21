<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Exam Schedule')]
class extends Component {

    public string $exam_id = '';
    public string $class_id = '';
    public string $subject_id = '';
    public string $exam_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $room = '';
    public string $total_marks = '';
    public string $passing_marks = '';
    public string $instructions = '';

    #[Computed]
    public function exams()
    {
        return Exam::where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('start_date')
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
    public function subjects()
    {
        return Subject::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'room' => ['nullable', 'string', 'max:100'],
            'total_marks' => ['required', 'integer', 'min:1'],
            'passing_marks' => ['required', 'integer', 'min:0', 'lte:total_marks'],
            'instructions' => ['nullable', 'string'],
        ]);

        ExamSchedule::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'exam_id' => $validated['exam_id'],
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'],
            'exam_date' => $validated['exam_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'room' => $validated['room'] !== '' ? $validated['room'] : null,
            'total_marks' => $validated['total_marks'],
            'passing_marks' => $validated['passing_marks'],
            'instructions' => $validated['instructions'] !== '' ? $validated['instructions'] : null,
        ]);

        Flux::toast(variant: 'success', text: __('Exam schedule created successfully.'));

        $this->redirect(route('exam-schedules.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Exam') }}" variant="listbox" wire:model="exam_id" required>
                <flux:select.option value="">{{ __('Select Exam') }}</flux:select.option>
                @foreach($this->exams as $exam)
                    <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
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
            <flux:select label="{{ __('Subject') }}" variant="listbox" wire:model="subject_id" required>
                <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
                @foreach($this->subjects as $subject)
                    <flux:select.option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</flux:select.option>
                @endforeach
            </flux:select>
            <flux:date-picker label="{{ __('Exam Date') }}" wire:model="exam_date" required />
        </div>

        <div class="grid grid-cols-3 gap-4">
            <flux:time-picker label="{{ __('Start Time') }}" wire:model="start_time" required />
            <flux:time-picker label="{{ __('End Time') }}" wire:model="end_time" required />
            <flux:input label="{{ __('Room') }}" wire:model="room" placeholder="{{ __('e.g., Room 101') }}" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Total Marks') }}" type="number" wire:model="total_marks" min="1" required />
            <flux:input label="{{ __('Passing Marks') }}" type="number" wire:model="passing_marks" min="0" required />
        </div>

        <flux:textarea label="{{ __('Instructions') }}" wire:model="instructions" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-schedule')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
