<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\ExamResult;
use App\Models\Exam;
use App\Models\ExamSchedule;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Exam Results')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterExam = '';
    public string $filterSchedule = '';

    public ?int $resultIdToDelete = null;

    #[Computed]
    public function results()
    {
        $query = ExamResult::with(['examSchedule.exam', 'examSchedule.subject', 'examSchedule.class', 'student.user', 'enteredBy'])
            ->orderByDesc('created_at');

        // Students can only see their own results
        if (Auth::user()->role === 'student') {
            $query->where('student_id', Auth::user()->student?->id);
        }

        if ($this->filterSchedule !== '') {
            $query->where('exam_schedule_id', $this->filterSchedule);
        } elseif ($this->filterExam !== '') {
            $query->whereHas('examSchedule', function ($q): void {
                $q->where('exam_id', $this->filterExam);
            });
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function exams()
    {
        return Exam::orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function schedules()
    {
        $query = ExamSchedule::with(['exam', 'subject', 'class']);

        if ($this->filterExam !== '') {
            $query->where('exam_id', $this->filterExam);
        }

        return $query->orderByDesc('exam_date')->get();
    }

    #[Computed]
    public function totalResults(): int
    {
        return (int) ExamResult::count();
    }

    public function updatedFilterExam(): void
    {
        $this->filterSchedule = '';
        $this->resetPage();
        unset($this->schedules);
    }

    public function updatedFilterSchedule(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterExam = '';
        $this->filterSchedule = '';
        $this->resetPage();
        unset($this->schedules);
    }

    public function confirmDelete(int $id): void
    {
        $this->resultIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this result?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->resultIdToDelete) {
            return;
        }

        ExamResult::findOrFail($this->resultIdToDelete)
            ->delete();

        $this->resultIdToDelete = null;
        unset($this->results);

        Flux::toast(variant: 'success', text: __('Result deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Exam Results') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View and manage student exam results.') }}</p>
        </div>

        <div class="flex gap-2">
            <flux:button variant="subtle" href="{{ route('results.check') }}" wire:navigate icon="magnifying-glass">
                {{ __('Check Results') }}
            </flux:button>
            <flux:button class="button" href="{{ route('results.create') }}" wire:navigate icon="plus">
                {{ __('Enter Results') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterExam" placeholder="{{ __('All Exams') }}">
                <flux:select.option value="">{{ __('All Exams') }}</flux:select.option>
                @foreach($this->exams as $exam)
                    <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterSchedule" placeholder="{{ __('All Schedules') }}">
                <flux:select.option value="">{{ __('All Schedules') }}</flux:select.option>
                @foreach($this->schedules as $schedule)
                    <flux:select.option value="{{ $schedule->id }}">
                        {{ $schedule->class?->name }} - {{ $schedule->subject?->name }} ({{ $schedule->exam_date?->format('M d') }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->results->count())
            <flux:table :paginate="$this->results">
                <flux:table.columns>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Exam') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Marks') }}</flux:table.column>
                    <flux:table.column>{{ __('Grade') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->results as $result)
                    <flux:table.rows>
                        <flux:table.row :key="$result->id">
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $result->student?->user?->first_name }} {{ $result->student?->user?->last_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $result->student?->admission_number ?? '-' }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $result->examSchedule?->exam?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $result->examSchedule?->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $result->examSchedule?->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($result->is_absent)
                                    <span class="text-gray-400">-</span>
                                @else
                                    {{ number_format((float) $result->marks_obtained, 2) }} / {{ number_format((float) $result->total_marks, 2) }}
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
                                    <flux:badge color="red">{{ __('Absent') }}</flux:badge>
                                @elseif((float) $result->marks_obtained >= (float) ($result->examSchedule?->passing_marks ?? 0))
                                    <flux:badge color="green">{{ __('Pass') }}</flux:badge>
                                @else
                                    <flux:badge color="red">{{ __('Fail') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-result'), $wire.dispatch('edit-result', { id: {{ $result->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $result->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Results') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Enter results for an exam schedule to see them here.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="edit-result" title="{{ __('Edit Result') }}" size="lg">
        <livewire:pages::app.exams.results.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
