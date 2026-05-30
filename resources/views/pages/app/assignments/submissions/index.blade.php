<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;

new #[Title('Submissions')]
class extends Component {
    use WithPagination;

    public string $filterAssignment = '';
    public string $filterStatus = '';

    #[Computed]
    public function submissions()
    {
        $query = AssignmentSubmission::where('tenant_id', Auth::user()->tenant_id)
            ->with(['assignment.class', 'assignment.subject', 'student.user'])
            ->orderByDesc('submitted_at');

        if ($this->filterAssignment !== '') {
            $query->where('assignment_id', $this->filterAssignment);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function assignments()
    {
        return Assignment::where('tenant_id', Auth::user()->tenant_id)
            ->with(['class', 'subject'])
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function totalSubmissions(): int
    {
        return (int) AssignmentSubmission::where('tenant_id', Auth::user()->tenant_id)->count();
    }

    #[Computed]
    public function pendingGrading(): int
    {
        return (int) AssignmentSubmission::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'submitted')
            ->whereNull('marks_obtained')
            ->count();
    }

    public function updatedFilterAssignment(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterAssignment = '';
        $this->filterStatus = '';
        $this->resetPage();
    }
};
?>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Submissions') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View and grade student assignment submissions.') }}</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Submissions') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalSubmissions) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Pending Grading') }}</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->pendingGrading) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterAssignment" placeholder="{{ __('All Assignments') }}">
                <flux:select.option value="">{{ __('All Assignments') }}</flux:select.option>
                @foreach($this->assignments as $assignment)
                    <flux:select.option value="{{ $assignment->id }}">
                        {{ $assignment->title }} ({{ $assignment->class?->name }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="submitted">{{ __('Submitted') }}</flux:select.option>
                <flux:select.option value="graded">{{ __('Graded') }}</flux:select.option>
                <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->submissions->count())
            <flux:table :paginate="$this->submissions">
                <flux:table.columns>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Assignment') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Submitted') }}</flux:table.column>
                    <flux:table.column>{{ __('Marks') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->submissions as $submission)
                    <flux:table.rows>
                        <flux:table.row :key="$submission->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $submission->student?->user?->first_name }} {{ $submission->student?->user?->last_name }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $submission->assignment?->title }}</flux:table.cell>
                            <flux:table.cell>{{ $submission->assignment?->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $submission->submitted_at?->format('M d, Y H:i') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($submission->marks_obtained !== null)
                                    {{ $submission->marks_obtained }} / {{ $submission->assignment?->total_marks ?? '-' }}
                                @else
                                    <span class="text-gray-400">{{ __('Not graded') }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($submission->status) {
                                        'graded' => 'green',
                                        'submitted' => 'blue',
                                        'late' => 'red',
                                        default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($submission->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="subtle" icon="eye" :href="route('submissions.show', $submission->assignment_id)" wire:navigate />
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Submissions') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Student submissions will appear here.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
