<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

new #[Title('Check Results')]
class extends Component {

    public string $exam_id = '';
    public string $student_id = '';
    public bool $resultsLoaded = false;
    public array $results = [];
    public array $summary = [];

    #[Computed]
    public function exams()
    {
        return Exam::where('result_published', true)
            ->orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function students()
    {
        return Student::where('status', 'active')
            ->with(['user', 'class'])
            ->orderBy('roll_number')
            ->get();
    }

    public function updatedExamId(): void
    {
        $this->results = [];
        $this->summary = [];
        $this->resultsLoaded = false;
    }

    public function updatedStudentId(): void
    {
        $this->results = [];
        $this->summary = [];
        $this->resultsLoaded = false;
    }

    public function checkResults(): void
    {
        $this->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $scheduleIds = ExamSchedule::where('exam_id', $this->exam_id)
            ->pluck('id');

        $examResults = ExamResult::where('student_id', $this->student_id)
            ->whereIn('exam_schedule_id', $scheduleIds)
            ->with(['examSchedule.subject', 'examSchedule.class'])
            ->get();

        $this->results = $examResults->map(function (ExamResult $result): array {
            $passingMarks = $result->examSchedule?->passing_marks ?? 0;

            return [
                'subject' => $result->examSchedule?->subject?->name ?? '-',
                'class' => $result->examSchedule?->class?->name ?? '-',
                'marks_obtained' => (float) $result->marks_obtained,
                'total_marks' => (float) $result->total_marks,
                'percentage' => $result->total_marks > 0 ? round(((float) $result->marks_obtained / (float) $result->total_marks) * 100, 2) : 0,
                'grade' => $result->grade ?? '-',
                'is_absent' => $result->is_absent,
                'passed' => ! $result->is_absent && (float) $result->marks_obtained >= $passingMarks,
                'remarks' => $result->remarks ?? '',
            ];
        })->values()->all();

        $totalMarks = array_sum(array_column($this->results, 'total_marks'));
        $totalObtained = array_sum(array_column($this->results, 'marks_obtained'));
        $totalSubjects = count($this->results);
        $passedSubjects = count(array_filter($this->results, fn (array $r): bool => $r['passed']));
        $absentCount = count(array_filter($this->results, fn (array $r): bool => $r['is_absent']));

        $this->summary = [
            'total_subjects' => $totalSubjects,
            'passed_subjects' => $passedSubjects,
            'failed_subjects' => $totalSubjects - $passedSubjects - $absentCount,
            'absent_count' => $absentCount,
            'total_marks' => $totalMarks,
            'marks_obtained' => $totalObtained,
            'percentage' => $totalMarks > 0 ? round(($totalObtained / $totalMarks) * 100, 2) : 0,
            'overall_status' => ($passedSubjects + $absentCount) === $totalSubjects && $absentCount < $totalSubjects ? 'Pass' : 'Fail',
        ];

        $this->resultsLoaded = true;
    }
};
?>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Check Results') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Look up exam results for a student.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('results.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:select label="{{ __('Exam') }}" variant="listbox" wire:model.live="exam_id" searchable required>
                <flux:select.option value="">{{ __('Select Exam') }}</flux:select.option>
                @foreach($this->exams as $exam)
                    <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select label="{{ __('Student') }}" variant="listbox" wire:model.live="student_id" searchable required>
                <flux:select.option value="">{{ __('Select Student') }}</flux:select.option>
                @foreach($this->students as $student)
                    <flux:select.option value="{{ $student->id }}">
                        {{ $student->user?->first_name }} {{ $student->user?->last_name }} ({{ $student->class?->name ?? '-' }})
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="mt-4">
            <flux:button
                wire:click="checkResults"
                variant="primary"
                class="button"
                icon="magnifying-glass"
                :disabled="$exam_id === '' || $student_id === ''"
            >
                {{ __('Check Results') }}
            </flux:button>
        </div>
    </flux:card>

    @if($resultsLoaded)
        @if(count($results) > 0)
            <div class="flex justify-end mb-2">
                <flux:button
                    variant="subtle"
                    icon="arrow-down-tray"
                    :href="route('results.download', [$student_id, $exam_id])"
                    target="_blank"
                >
                    {{ __('Download Result Sheet') }}
                </flux:button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <flux:card>
                    <p class="text-sm text-gray-500">{{ __('Overall') }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $summary['overall_status'] === 'Pass' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ __($summary['overall_status']) }}
                    </p>
                </flux:card>
                <flux:card>
                    <p class="text-sm text-gray-500">{{ __('Percentage') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['percentage'] }}%</p>
                </flux:card>
                <flux:card>
                    <p class="text-sm text-gray-500">{{ __('Total Marks') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['marks_obtained'] }} / {{ $summary['total_marks'] }}</p>
                </flux:card>
                <flux:card>
                    <p class="text-sm text-gray-500">{{ __('Subjects') }}</p>
                    <p class="mt-2 text-sm text-gray-900 dark:text-white">
                        <span class="text-green-600">{{ $summary['passed_subjects'] }} {{ __('passed') }}</span> &middot;
                        <span class="text-red-600">{{ $summary['failed_subjects'] }} {{ __('failed') }}</span>
                        @if($summary['absent_count'] > 0)
                            &middot; <span class="text-gray-500">{{ $summary['absent_count'] }} {{ __('absent') }}</span>
                        @endif
                    </p>
                </flux:card>
            </div>

            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Subject') }}</flux:table.column>
                        <flux:table.column>{{ __('Marks') }}</flux:table.column>
                        <flux:table.column>{{ __('Percentage') }}</flux:table.column>
                        <flux:table.column>{{ __('Grade') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Remarks') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach($results as $result)
                        <flux:table.rows>
                            <flux:table.row>
                                <flux:table.cell>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $result['subject'] }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($result['is_absent'])
                                        <span class="text-gray-400">-</span>
                                    @else
                                        {{ $result['marks_obtained'] }} / {{ $result['total_marks'] }}
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($result['is_absent'])
                                        <span class="text-gray-400">-</span>
                                    @else
                                        {{ $result['percentage'] }}%
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($result['grade'] !== '-')
                                        <flux:badge color="blue">{{ $result['grade'] }}</flux:badge>
                                    @else
                                        -
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($result['is_absent'])
                                        <flux:badge color="gray">{{ __('Absent') }}</flux:badge>
                                    @elseif($result['passed'])
                                        <flux:badge color="green">{{ __('Pass') }}</flux:badge>
                                    @else
                                        <flux:badge color="red">{{ __('Fail') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $result['remarks'] ?: '-' }}</flux:table.cell>
                            </flux:table.row>
                        </flux:table.rows>
                    @endforeach
                </flux:table>
            </flux:card>
        @else
            <flux:card>
                <div class="p-6 text-center">
                    <flux:icon name="document-magnifying-glass" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Results Found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No results found for this student in the selected exam.') }}</p>
                </div>
            </flux:card>
        @endif
    @endif
</div>
