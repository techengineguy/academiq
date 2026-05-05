<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Timetable;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\TimeSlot;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public ?Timetable $timetable = null;

    public string $class_id = '';
    public string $section_id = '';
    public string $subject_id = '';
    public string $teacher_id = '';
    public string $time_slot_id = '';
    public string $day = '';
    public string $room = '';
    public string $academic_year_id = '';

    #[On('edit-timetable')]
    public function loadTimetable(string $uuid): void
    {
        $this->timetable = Timetable::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)->firstOrFail();

        $this->class_id = $this->timetable->class_id;
        $this->section_id = $this->timetable->section_id;
        $this->subject_id = $this->timetable->subject_id;
        $this->teacher_id = $this->timetable->teacher_id;
        $this->time_slot_id = $this->timetable->time_slot_id;
        $this->day = $this->timetable->day;
        $this->room = $this->timetable->room;
        $this->academic_year_id = $this->timetable->academic_year_id;
    }

    public function update(): void
    {
        $this->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'day' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'room' => ['nullable', 'string', 'max:50'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $this->timetable->update([
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
            'time_slot_id' => $this->time_slot_id,
            'day' => $this->day,
            'room' => $this->room,
            'academic_year_id' => $this->academic_year_id,
        ]);

        Flux::toast(variant: 'success', text: __('Timetable entry updated successfully.'));

        $this->redirect(route('timetables.index'), navigate: true);
    }
};
?>

<div>
    @if($this->timetable)
        <form wire:submit="update" class="space-y-6">
            <flux:select
                label="{{ __('Academic Year') }}"
                wire:model="academic_year_id"
                required
            >
                <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                @forelse(AcademicYear::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->get() as $year)
                    <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select
                label="{{ __('Class') }}"
                wire:model="class_id"
                required
            >
                <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select
                label="{{ __('Section') }}"
                wire:model="section_id"
                required
            >
                <flux:select.option value="">{{ __('Select Section') }}</flux:select.option>
                @forelse(Section::where('tenant_id', Auth::user()->tenant_id)->get() as $section)
                    <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select
                label="{{ __('Subject') }}"
                wire:model="subject_id"
                required
            >
                <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
                @forelse(Subject::where('tenant_id', Auth::user()->tenant_id)->get() as $subject)
                    <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select
                label="{{ __('Teacher') }}"
                wire:model="teacher_id"
                required
            >
                <flux:select.option value="">{{ __('Select Teacher') }}</flux:select.option>
                @forelse(User::where('tenant_id', Auth::user()->tenant_id)->where('role', 'teacher')->get() as $teacher)
                    <flux:select.option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select
                label="{{ __('Time Slot') }}"
                wire:model="time_slot_id"
                required
            >
                <flux:select.option value="">{{ __('Select Time Slot') }}</flux:select.option>
                @forelse(TimeSlot::where('tenant_id', Auth::user()->tenant_id)->get() as $slot)
                    <flux:select.option value="{{ $slot->id }}">{{ $slot->start_time->format('H:i') }} - {{ $slot->end_time->format('H:i') }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select
                label="{{ __('Day') }}"
                wire:model="day"
                required
            >
                <flux:select.option value="monday">{{ __('Monday') }}</flux:select.option>
                <flux:select.option value="tuesday">{{ __('Tuesday') }}</flux:select.option>
                <flux:select.option value="wednesday">{{ __('Wednesday') }}</flux:select.option>
                <flux:select.option value="thursday">{{ __('Thursday') }}</flux:select.option>
                <flux:select.option value="friday">{{ __('Friday') }}</flux:select.option>
                <flux:select.option value="saturday">{{ __('Saturday') }}</flux:select.option>
                <flux:select.option value="sunday">{{ __('Sunday') }}</flux:select.option>
            </flux:select>

            <flux:input
                label="{{ __('Room') }}"
                wire:model="room"
            />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-timetable')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>


