<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('Mark Attendance')]
#[Layout('layouts.teacher')]
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
    public function myClassSections()
    {
        $classIds = ClassSubject::where('tenant_id', Auth::user()->tenant_id)
            ->where('teacher_id', Auth::id())
            ->pluck('class_id')
            ->unique();

        return Section::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('class_id', $classIds)
            ->with('class')
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

        $existing = Attendance::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $this->date)
            ->pluck('status', 'student_id');

        $this->rows = $students->map(fn (Student $s) => [
            'student_id' => $s->id,
            'name' => trim($s->user?->first_name . ' ' . $s->user?->last_name),
            'roll_number' => $s->roll_number ?? '-',
            'status' => $existing[$s->id] ?? 'present',
        ])->values()->all();

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
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        Flux::toast(variant: 'success', text: __('Attendance saved successfully.'));

        $this->rows = [];
        $this->studentsLoaded = false;
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Mark Attendance') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Mark attendance for your assigned classes.') }}</p>
    </div>

    <flux:card>
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:select label="{{ __('Class & Section') }}" variant="listbox" wire:model.live="class_section" required>
                <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                @foreach($this->myClassSections as $section)
                    <flux:select.option value="{{ $section->class_id }}-{{ $section->id }}">
                        {{ $section->class?->name }}-{{ $section->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:date-picker label="{{ __('Date') }}" wire:model.live="date" required />
        </div>

        <div class="mt-4">
            <flux:button wire:click="loadStudents" variant="primary" class="button" icon="users" :disabled="$class_section === '' || $date === ''">
                {{ __('Load Students') }}
            </flux:button>
        </div>
    </flux:card>

    @if($studentsLoaded && count($rows) > 0)
        <form wire:submit="save" class="space-y-4">
            <flux:card>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ __('Students') }}
                            <flux:badge variant="info" class="ml-2">{{ count($rows) }}</flux:badge>
                        </h2>
                    </div>
                    <div class="flex gap-2">
                        <flux:button type="button" size="sm" variant="subtle" wire:click="markAll('present')">{{ __('All Present') }}</flux:button>
                        <flux:button type="button" size="sm" variant="subtle" wire:click="markAll('absent')">{{ __('All Absent') }}</flux:button>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($rows as $index => $row)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-zinc-700">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $row['roll_number'] }}</span>
                            </div>
                            <flux:select variant="listbox" wire:model="rows.{{ $index }}.status" class="w-32">
                                <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                                <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                                <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                                <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                                <flux:select.option value="excused">{{ __('Excused') }}</flux:select.option>
                            </flux:select>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:button type="submit" variant="primary" class="button" icon="check">
                {{ __('Save Attendance') }}
            </flux:button>
        </form>
    @elseif($studentsLoaded)
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Students') }}</h3>
            </div>
        </flux:card>
    @endif
</div>
</div>
