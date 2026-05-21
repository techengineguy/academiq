<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Attendance;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Mark Student Attendance')]
class extends Component {

    public string $class_section = '';
    public string $date = '';
    public array $rows = [];
    public bool $studentsLoaded = false;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    #[Computed]
    public function classSections()
    {
        return Section::where('tenant_id', Auth::user()->tenant_id)
            ->with('class')
            ->whereHas('class')
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();
    }

    public function updatedClassSection(): void
    {
        $this->rows = [];
        $this->studentsLoaded = false;
    }

    public function loadStudents(): void
    {
        $this->validate([
            'class_section' => ['required'],
            'date' => ['required', 'date'],
        ]);

        [$classId, $sectionId] = explode('-', $this->class_section);

        $students = Student::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('status', 'active')
            ->with('user')
            ->orderBy('roll_number')
            ->get();

        $existingAttendances = Attendance::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $this->date)
            ->pluck('status', 'student_id');

        $this->rows = $students->map(function (Student $student) use ($existingAttendances): array {
            return [
                'student_id' => $student->id,
                'name' => trim($student->user?->first_name . ' ' . $student->user?->last_name),
                'roll_number' => $student->roll_number ?? '-',
                'admission_number' => $student->admission_number ?? '-',
                'status' => $existingAttendances[$student->id] ?? 'present',
                'check_in_time' => '',
                'check_out_time' => '',
                'remarks' => '',
            ];
        })->values()->all();

        $this->studentsLoaded = true;
    }

    public function markAll(string $status): void
    {
        foreach ($this->rows as $index => $row) {
            $this->rows[$index]['status'] = $status;
        }
    }

    public function save(): void
    {
        $this->validate([
            'class_section' => ['required'],
            'date' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', 'exists:students,id'],
            'rows.*.status' => ['required', 'in:present,absent,late,half_day,excused'],
            'rows.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'rows.*.check_out_time' => ['nullable', 'date_format:H:i'],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        [$classId, $sectionId] = explode('-', $this->class_section);

        DB::transaction(function () use ($classId, $sectionId): void {
            foreach ($this->rows as $row) {
                Attendance::updateOrCreate(
                    [
                        'tenant_id' => Auth::user()->tenant_id,
                        'student_id' => $row['student_id'],
                        'date' => $this->date,
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'class_id' => $classId,
                        'section_id' => $sectionId,
                        'status' => $row['status'],
                        'check_in_time' => $row['check_in_time'] !== '' ? $row['check_in_time'] : null,
                        'check_out_time' => $row['check_out_time'] !== '' ? $row['check_out_time'] : null,
                        'remarks' => $row['remarks'] !== '' ? $row['remarks'] : null,
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        Flux::toast(variant: 'success', text: __('Attendance marked successfully.'));

        $this->redirect(route('attendance.index'), navigate: true);
    }
};
?>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Mark Student Attendance') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Select a class and section to mark attendance for the day.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('attendance.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:select
                label="{{ __('Class & Section') }}"
                variant="listbox"
                wire:model.live="class_section"
                required
            >
                <flux:select.option value="">{{ __('Select Class & Section') }}</flux:select.option>
                @foreach($this->classSections as $section)
                    <flux:select.option value="{{ $section->class_id }}-{{ $section->id }}">
                        {{ $section->class?->name }}-{{ $section->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:date-picker label="{{ __('Date') }}" wire:model.live="date" required />
        </div>

        <div class="mt-4">
            <flux:button
                wire:click="loadStudents"
                variant="primary"
                class="button"
                icon="users"
                :disabled="$class_section === '' || $date === ''"
            >
                {{ __('Load Students') }}
            </flux:button>
        </div>
    </flux:card>

    @if($studentsLoaded)
        @if(count($rows) > 0)
            <form wire:submit="save" class="space-y-4">
                <flux:card>
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('Students') }}
                                <flux:badge variant="info" class="ml-2">{{ count($rows) }}</flux:badge>
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Set attendance status for each student.') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <flux:button type="button" size="sm" variant="subtle" wire:click="markAll('present')">
                                {{ __('All Present') }}
                            </flux:button>
                            <flux:button type="button" size="sm" variant="subtle" wire:click="markAll('absent')">
                                {{ __('All Absent') }}
                            </flux:button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($rows as $index => $row)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-zinc-700">
                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                    <div class="xl:col-span-2 flex flex-col justify-center">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ __('Roll') }}: {{ $row['roll_number'] }} &middot; {{ $row['admission_number'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <flux:select
                                            label="{{ __('Status') }}"
                                            variant="listbox"
                                            wire:model="rows.{{ $index }}.status"
                                        >
                                            <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                                            <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                                            <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                                            <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                                            <flux:select.option value="excused">{{ __('Excused') }}</flux:select.option>
                                        </flux:select>
                                    </div>

                                    <div>
                                        <flux:time-picker
                                            label="{{ __('Check In') }}"
                                            wire:model="rows.{{ $index }}.check_in_time"
                                        />
                                    </div>

                                    <div>
                                        <flux:time-picker
                                            label="{{ __('Check Out') }}"
                                            wire:model="rows.{{ $index }}.check_out_time"
                                        />
                                    </div>

                                    <div>
                                        <flux:input
                                            label="{{ __('Remarks') }}"
                                            wire:model="rows.{{ $index }}.remarks"
                                            placeholder="{{ __('Optional') }}"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary" class="button" icon="check">
                        {{ __('Save Attendance') }}
                    </flux:button>
                    <flux:button type="button" variant="subtle" href="{{ route('attendance.index') }}" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        @else
            <flux:card>
                <div class="p-6 text-center">
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Active Students') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('There are no active students in the selected class and section.') }}</p>
                </div>
            </flux:card>
        @endif
    @endif
</div>
