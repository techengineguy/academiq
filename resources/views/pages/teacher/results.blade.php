<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\GradeScale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('Enter Results')]
#[Layout('layouts.teacher')]
class extends Component {

    public string $exam_schedule_id = '';
    public array $rows = [];
    public bool $studentsLoaded = false;

    #[Computed]
    public function mySchedules()
    {
        $classIds = ClassSubject::where('teacher_id', Auth::id())
            ->pluck('class_id')
            ->unique();

        return ExamSchedule::whereIn('class_id', $classIds)
            ->with(['exam', 'class', 'subject'])
            ->orderByDesc('exam_date')
            ->get();
    }

    public function updatedExamScheduleId(): void
    {
        $this->rows = [];
        $this->studentsLoaded = false;
    }

    public function loadStudents(): void
    {
        $this->validate(['exam_schedule_id' => ['required', 'exists:exam_schedules,id']]);

        $schedule = ExamSchedule::findOrFail($this->exam_schedule_id);

        $students = Student::where('class_id', $schedule->class_id)
            ->where('status', 'active')
            ->with('user')
            ->orderBy('roll_number')
            ->get();

        $existing = ExamResult::where('exam_schedule_id', $this->exam_schedule_id)
            ->get()->keyBy('student_id');

        $this->rows = $students->map(fn (Student $s) => [
            'student_id' => $s->id,
            'name' => trim($s->user?->first_name . ' ' . $s->user?->last_name),
            'roll_number' => $s->roll_number ?? '-',
            'marks_obtained' => isset($existing[$s->id]) ? (string) $existing[$s->id]->marks_obtained : '',
            'total_marks' => (string) $schedule->total_marks,
            'is_absent' => isset($existing[$s->id]) ? $existing[$s->id]->is_absent : false,
        ])->values()->all();

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
        ]);

        $schedule = ExamSchedule::findOrFail($this->exam_schedule_id);
        $gradeScales = GradeScale::orderByDesc('min_percentage')->get();

        DB::transaction(function () use ($schedule, $gradeScales): void {
            foreach ($this->rows as $row) {
                $isAbsent = (bool) $row['is_absent'];
                $marks = $isAbsent ? 0 : (float) ($row['marks_obtained'] ?: 0);
                $total = (float) $schedule->total_marks;
                $pct = $total > 0 ? ($marks / $total) * 100 : 0;

                $grade = null;
                if (! $isAbsent) {
                    foreach ($gradeScales as $scale) {
                        if ($pct >= (float) $scale->min_percentage && $pct <= (float) $scale->max_percentage) {
                            $grade = $scale->grade;
                            break;
                        }
                    }
                }

                ExamResult::updateOrCreate(
                    ['tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid, 'exam_schedule_id' => $this->exam_schedule_id, 'student_id' => $row['student_id']],
                    ['uuid' => Str::uuid(), 'marks_obtained' => number_format($marks, 2, '.', ''), 'total_marks' => number_format($total, 2, '.', ''), 'grade' => $grade, 'is_absent' => $isAbsent, 'entered_by' => Auth::id()]
                );
            }
        });

        Flux::toast(variant: 'success', text: __('Results saved successfully.'));
        $this->rows = [];
        $this->studentsLoaded = false;
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Enter Results') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Enter marks for your assigned exam schedules.') }}</p>
    </div>

    <flux:card>
        <flux:select label="{{ __('Exam Schedule') }}" variant="listbox" wire:model.live="exam_schedule_id" required>
            <flux:select.option value="">{{ __('Select Schedule') }}</flux:select.option>
            @foreach($this->mySchedules as $schedule)
                <flux:select.option value="{{ $schedule->id }}">
                    {{ $schedule->exam?->name }} - {{ $schedule->class?->name }} - {{ $schedule->subject?->name }} ({{ $schedule->exam_date?->format('M d, Y') }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="mt-4">
            <flux:button wire:click="loadStudents" variant="primary" class="button" icon="users" :disabled="$exam_schedule_id === ''">
                {{ __('Load Students') }}
            </flux:button>
        </div>
    </flux:card>

    @if($studentsLoaded && count($rows) > 0)
        <form wire:submit="save" class="space-y-4">
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('Students') }} <flux:badge variant="info" class="ml-2">{{ count($rows) }}</flux:badge>
                    </h2>
                </div>

                <div class="space-y-2">
                    @foreach($rows as $index => $row)
                        <div class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-zinc-700">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $row['roll_number'] }} &middot; /{{ $row['total_marks'] }}</span>
                            </div>
                            <div class="w-24">
                                <flux:input type="text" inputmode="decimal" wire:model="rows.{{ $index }}.marks_obtained" placeholder="0" :disabled="$row['is_absent']" />
                            </div>
                            <flux:checkbox label="{{ __('Absent') }}" wire:model.live="rows.{{ $index }}.is_absent" />
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:button type="submit" variant="primary" class="button" icon="check">
                {{ __('Save Results') }}
            </flux:button>
        </form>
    @endif
</div>
</div>
