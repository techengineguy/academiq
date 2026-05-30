<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('New Allocation')]
class extends Component {

    public string $student_id = '';
    public string $hostel_room_id = '';
    public string $academic_year_id = '';
    public string $allocated_date = '';
    public string $bed_number = '';
    public string $remarks = '';

    public function mount(): void
    {
        $this->allocated_date = now()->format('Y-m-d');

        $currentYear = AcademicYear::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_current', true)
            ->first();

        if ($currentYear) {
            $this->academic_year_id = (string) $currentYear->id;
        }
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
    public function rooms()
    {
        return HostelRoom::whereHas('hostelBuilding', fn ($q) => $q->where('tenant_id', Auth::user()->tenant_id))
            ->whereColumn('occupied', '<', 'capacity')
            ->where('status', 'available')
            ->with('hostelBuilding')
            ->orderBy('hostel_building_id')
            ->orderBy('room_number')
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
            'student_id' => ['required', 'exists:students,id'],
            'hostel_room_id' => ['required', 'exists:hostel_rooms,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'allocated_date' => ['required', 'date'],
            'bed_number' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated): void {
            $room = HostelRoom::findOrFail($validated['hostel_room_id']);

            if ($room->occupied >= $room->capacity) {
                throw new \Exception(__('Room is at full capacity.'));
            }

            HostelAllocation::create([
                'tenant_id' => Auth::user()->tenant_id,
                'uuid' => Str::uuid(),
                'student_id' => $validated['student_id'],
                'hostel_room_id' => $validated['hostel_room_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'allocated_date' => $validated['allocated_date'],
                'bed_number' => $validated['bed_number'] ?: null,
                'status' => 'active',
                'remarks' => $validated['remarks'] ?: null,
            ]);

            $room->increment('occupied');

            if ($room->occupied >= $room->capacity) {
                $room->update(['status' => 'occupied']);
            }
        });

        Flux::toast(variant: 'success', text: __('Allocation created successfully.'));

        $this->redirect(route('hostel-allocations.index'), navigate: true);
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

        <flux:select label="{{ __('Room') }}" variant="listbox" wire:model="hostel_room_id" required>
            <flux:select.option value="">{{ __('Select Available Room') }}</flux:select.option>
            @foreach($this->rooms as $room)
                <flux:select.option value="{{ $room->id }}">
                    {{ $room->hostelBuilding?->name }} - {{ __('Room') }} {{ $room->room_number }}
                    ({{ $room->occupied }}/{{ $room->capacity }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
                <flux:select.option value="">{{ __('Select') }}</flux:select.option>
                @foreach($this->academicYears as $year)
                    <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:date-picker label="{{ __('Allocated Date') }}" wire:model="allocated_date" required />
        </div>

        <flux:input label="{{ __('Bed Number') }}" wire:model="bed_number" placeholder="{{ __('e.g., B1, B2') }}" />

        <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Allocate') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-allocation')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
