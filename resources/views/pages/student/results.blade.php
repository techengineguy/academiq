<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\ExamResult;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Results')]
#[Layout('layouts.student')]
class extends Component {

    public string $filterExam = '';

    #[Computed]
    public function exams()
    {
        return Exam::where('tenant_id', Auth::user()->tenant_id)
            ->where('result_published', true)
            ->orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function results()
    {
        $student = Auth::user()->student;
        if (! $student) {
            return collect();
        }

        $query = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderByDesc('created_at');

        if ($this->filterExam !== '') {
            $query->whereHas('examSchedule', fn ($q) => $q->where('exam_id', $this->filterExam));
        }

        return $query->get();
    }

    #[Computed]
    public function summary(): array
    {
        $results = $this->results;
        if ($results->isEmpty()) {
            return [];
        }

        $totalMarks = $results->sum('total_marks');
        $obtained = $results->sum('marks_obtained');
        $percentage = $totalMarks > 0 ? round(($obtained / $totalMarks) * 100, 2) : 0;

        return [
            'total_subjects' => $results->count(),
            'total_marks' => $totalMarks,
            'marks_obtained' => $obtained,
            'percentage' => $percentage,
        ];
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Results') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View your exam results and performance.') }}</p>
    </div>

    <div class="mb-4">
        <flux:select variant="listbox" wire:model.live="filterExam" placeholder="{{ __('All Exams') }}">
            <flux:select.option value="">{{ __('All Exams') }}</flux:select.option>
            @foreach($this->exams as $exam)
                <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if(! empty($this->summary))
        <div class="grid gap-4 sm:grid-cols-4">
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Subjects') }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->summary['total_subjects'] }}</p>
            </flux:card>
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Marks Obtained') }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->summary['marks_obtained'] }}</p>
            </flux:card>
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Total Marks') }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->summary['total_marks'] }}</p>
            </flux:card>
            <flux:card>
                <p class="text-sm text-gray-500">{{ __('Percentage') }}</p>
                <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->summary['percentage'] }}%</p>
            </flux:card>
        </div>
    @endif

    <flux:card>
        @if($this->results->count())
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Exam') }}</flux:table.column>
                    <flux:table.column>{{ __('Marks') }}</flux:table.column>
                    <flux:table.column>{{ __('Grade') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->results as $result)
                    <flux:table.rows>
                        <flux:table.row>
                            <flux:table.cell>{{ $result->examSchedule?->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $result->examSchedule?->exam?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($result->is_absent)
                                    <span class="text-gray-400">-</span>
                                @else
                                    {{ $result->marks_obtained }} / {{ $result->total_marks }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($result->grade)
                                    <flux:badge color="blue">{{ $result->grade }}</flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($result->is_absent)
                                    <flux:badge color="gray">{{ __('Absent') }}</flux:badge>
                                @elseif((float) $result->marks_obtained >= (float) ($result->examSchedule?->passing_marks ?? 0))
                                    <flux:badge color="green">{{ __('Pass') }}</flux:badge>
                                @else
                                    <flux:badge color="red">{{ __('Fail') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Results') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Results will appear here once published.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
</div>
