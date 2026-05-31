<?php

use Livewire\Component;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\Timetable;
use App\Models\TimeSlot;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $class_id = '';
    public $section_id = '';
    public $subject_id = '';
    public $teacher_id = '';
    public $time_slot_id = '';
    public $day = '';
    public $room = '';
    public $academic_year_id = '';

    public function mount()
    {
        $currentYear = AcademicYear::where('tenant_id', Auth::user()->tenant_id)
        ->where('is_current', true)->first();
        if ($currentYear) {
            $this->academic_year_id = $currentYear->id;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'room' => 'nullable|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        Timetable::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'time_slot_id' => $validated['time_slot_id'],
            'day' => $validated['day'],
            'room' => $validated['room'],
            'academic_year_id' => $validated['academic_year_id'],
        ]);

        Flux::toast(variant: 'success', text: __('Timetable entry created successfully.'));

        $this->redirect(route('timetables.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
            <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
            @forelse(AcademicYear::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->get() as $year)
                <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Academic Years Available') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id" required>
            <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
            @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select label="{{ __('Section') }}" variant="listbox" wire:model="section_id" required>
            <flux:select.option value="">{{ __('Select Section') }}</flux:select.option>
            @forelse(Section::where('tenant_id', Auth::user()->tenant_id)->get() as $section)
                <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Sections Available') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select label="{{ __('Subject') }}" variant="listbox" wire:model="subject_id" required>
            <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
            @forelse(Subject::where('tenant_id', Auth::user()->tenant_id)->get() as $subject)
                <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Subjects Available') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select label="{{ __('Teacher') }}" variant="listbox" wire:model="teacher_id" searchable required>
            <flux:select.option value="">{{ __('Select Teacher') }}</flux:select.option>
            @forelse(User::where('tenant_id', Auth::user()->tenant_id)->where('role', 'teacher')->get() as $teacher)
                <flux:select.option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Teachers Available') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select label="{{ __('Time Slot') }}" variant="listbox" wire:model="time_slot_id" required>
            <flux:select.option value="">{{ __('Select Time Slot') }}</flux:select.option>
            @forelse(TimeSlot::where('tenant_id', Auth::user()->tenant_id)->get() as $slot)
                <flux:select.option value="{{ $slot->id }}">{{ $slot->start_time->format('H:i') }} - {{ $slot->end_time->format('H:i') }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Time Slots Available') }}</flux:select.option>
            @endforelse
        </flux:select>

        <flux:select label="{{ __('Day') }}" variant="listbox" wire:model="day" required>
            <flux:select.option value="">{{ __('Select Day') }}</flux:select.option>
            <flux:select.option value="monday">{{ __('Monday') }}</flux:select.option>
            <flux:select.option value="tuesday">{{ __('Tuesday') }}</flux:select.option>
            <flux:select.option value="wednesday">{{ __('Wednesday') }}</flux:select.option>
            <flux:select.option value="thursday">{{ __('Thursday') }}</flux:select.option>
            <flux:select.option value="friday">{{ __('Friday') }}</flux:select.option>
            <flux:select.option value="saturday">{{ __('Saturday') }}</flux:select.option>
            <flux:select.option value="sunday">{{ __('Sunday') }}</flux:select.option>
        </flux:select>

        <flux:input label="{{ __('Room') }}" placeholder="{{ __('Enter room number or name') }}" wire:model="room" />

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-timetable')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>


