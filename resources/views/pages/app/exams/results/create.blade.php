<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\Student;
use App\Models\GradeScale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Enter Exam Results')]
class extends Component {

    public string $exam_id = '';
    public string $exam_schedule_id = '';
    public array $rows = [];
    public bool $studentsLoaded = false;

    #[Computed]
    public function exams()
    {
        return Exam::orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function schedules()
    {
        if ($this->exam_id === '') {
            return collect();
        }

        return ExamSchedule::where('exam_id', $this->exam_id)
            ->with(['class', 'subject'])
            ->orderBy('exam_date')
            ->get();
    }

    public function updatedExamId(): void
    {
        $this->exam_schedule_id = '';
        $this->rows = [];
        $this->studentsLoaded = false;
        unset($this->schedules);
    }

    public function updatedExamScheduleId(): void
    {
        $this->rows = [];
        $this->studentsLoaded = false;
    }

    public function loadStudents(): void
    {
        $this->validate([
            'exam_schedule_id' => ['required', 'exists:exam_schedules,id'],
        ]);

        $schedule = ExamSchedule::findOrFail($this->exam_schedule_id);

        $students = Student::where('class_id', $schedule->class_id)
            ->where('status', 'active')
            ->with('user')
            ->orderBy('roll_number')
            ->get();

        $existingResults = ExamResult::where('exam_schedule_id', $this->exam_schedule_id)
            ->get()
            ->keyBy('student_id');

        $this->rows = $students->map(function (Student $student) use ($existingResults, $schedule): array {
            $existing = $existingResults[$student->id] ?? null;

            return [
                'student_id' => $student->id,
                'name' => trim($student->user?->first_name . ' ' . $student->user?->last_name),
                'roll_number' => $student->roll_number ?? '-',
                'marks_obtained' => $existing ? (string) $existing->marks_obtained : '',
                'total_marks' => (string) $schedule->total_marks,
                'is_absent' => $existing ? $existing->is_absent : false,
                'remarks' => $existing ? (string) ($existing->remarks ?? '') : '',
            ];
        })->values()->all();

        $this->studentsLoaded = true;
    }

    public function save(): void
    {
        $this->validate([
            'exam_schedule_id' => ['required', 'exists:exam_schedules,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', 'exists:students,id'],
            'rows.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'rows.*.is_absent' => ['boolean'],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $schedule = ExamSchedule::findOrFail($this->exam_schedule_id);

        $gradeScales = GradeScale::orderByDesc('min_percentage')
            ->get();

        DB::transaction(function () use ($schedule, $gradeScales): void {
            foreach ($this->rows as $row) {
                $isAbsent = (bool) $row['is_absent'];
                $marksObtained = $isAbsent ? 0 : (float) ($row['marks_obtained'] ?: 0);
                $totalMarks = (float) $schedule->total_marks;
                $percentage = $totalMarks > 0 ? ($marksObtained / $totalMarks) * 100 : 0;

                $grade = null;
                if (! $isAbsent) {
                    foreach ($gradeScales as $scale) {
                        if ($percentage >= (float) $scale->min_percentage && $percentage <= (float) $scale->max_percentage) {
                            $grade = $scale->grade;
                            break;
                        }
                    }
                }

                ExamResult::updateOrCreate(
                    [
                        'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                        'exam_schedule_id' => $this->exam_schedule_id,
                        'student_id' => $row['student_id'],
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'marks_obtained' => number_format($marksObtained, 2, '.', ''),
                        'total_marks' => number_format($totalMarks, 2, '.', ''),
                        'grade' => $grade,
                        'is_absent' => $isAbsent,
                        'remarks' => $row['remarks'] !== '' ? $row['remarks'] : null,
                        'entered_by' => Auth::id(),
                    ]
                );
            }
        });

        Flux::toast(variant: 'success', text: __('Results saved successfully.'));

        $this->redirect(route('results.index'), navigate: true);
    }
};
?>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Enter Exam Results') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Select an exam schedule and enter marks for each student.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('results.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:select label="{{ __('Exam') }}" variant="listbox" wire:model.live="exam_id" required>
                <flux:select.option value="">{{ __('Select Exam') }}</flux:select.option>
                @foreach($this->exams as $exam)
                    <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select label="{{ __('Schedule') }}" variant="listbox" wire:model.live="exam_schedule_id" :disabled="$exam_id === ''" required>
                <flux:select.option value="">{{ __('Select Schedule') }}</flux:select.option>
                @foreach($this->schedules as $schedule)
                    <flux:select.option value="{{ $schedule->id }}">
                        {{ $schedule->class?->name }} - {{ $schedule->subject?->name }} ({{ $schedule->exam_date?->format('M d, Y') }})
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="mt-4">
            <flux:button
                wire:click="loadStudents"
                variant="primary"
                class="button"
                icon="users"
                :disabled="$exam_schedule_id === ''"
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
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Enter marks for each student. Check absent if the student did not appear.') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($rows as $index => $row)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-zinc-700">
                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                    <div class="xl:col-span-2 flex flex-col justify-center">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ __('Roll') }}: {{ $row['roll_number'] }} &middot; {{ __('Total') }}: {{ $row['total_marks'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <flux:input
                                            label="{{ __('Marks') }}"
                                            type="text"
                                            inputmode="decimal"
                                            wire:model="rows.{{ $index }}.marks_obtained"
                                            :disabled="$row['is_absent']"
                                            placeholder="0"
                                        />
                                    </div>

                                    <div class="flex items-end">
                                        <flux:checkbox
                                            label="{{ __('Absent') }}"
                                            wire:model.live="rows.{{ $index }}.is_absent"
                                        />
                                    </div>

                                    <div class="xl:col-span-2">
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
                        {{ __('Save Results') }}
                    </flux:button>
                    <flux:button type="button" variant="subtle" href="{{ route('results.index') }}" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        @else
            <flux:card>
                <div class="p-6 text-center">
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Students') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No active students found for the selected class.') }}</p>
                </div>
            </flux:card>
        @endif
    @endif
</div>
