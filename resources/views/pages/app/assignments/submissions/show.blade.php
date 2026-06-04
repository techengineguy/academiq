<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('View Submissions')]
class extends Component {

    public int $id;
    public array $grades = [];

    public function mount(int $id): void
    {
        $this->id = $id;

        // Pre-fill grades from existing marks
        foreach ($this->submissions as $submission) {
            $this->grades[$submission->id] = [
                'marks_obtained' => (string) ($submission->marks_obtained ?? ''),
                'feedback' => (string) ($submission->feedback ?? ''),
            ];
        }
    }

    #[Computed]
    public function assignment()
    {
        return Assignment::with(['class', 'section', 'subject', 'teacher'])
            ->findOrFail($this->id);
    }

    #[Computed]
    public function submissions()
    {
        return AssignmentSubmission::where('assignment_id', $this->id)
            ->with(['student.user'])
            ->orderBy('submitted_at')
            ->get();
    }

    public function saveGrades(): void
    {
        $this->validate([
            'grades.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'grades.*.feedback' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($this->grades as $submissionId => $grade) {
            $submission = AssignmentSubmission::where('assignment_id', $this->id)
                ->find($submissionId);

            if (! $submission) {
                continue;
            }

            $marks = $grade['marks_obtained'] !== '' ? (float) $grade['marks_obtained'] : null;

            $submission->update([
                'marks_obtained' => $marks !== null ? number_format($marks, 2, '.', '') : null,
                'feedback' => $grade['feedback'] !== '' ? $grade['feedback'] : null,
                'status' => $marks !== null ? 'graded' : $submission->status,
            ]);
        }

        Flux::toast(variant: 'success', text: __('Grades saved successfully.'));
        unset($this->submissions);
    }
};
?>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Submissions') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Review and grade student submissions.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('submissions.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="text-xs text-gray-500">{{ __('Assignment') }}</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $this->assignment->title }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Class') }}</p>
                <p class="text-gray-900 dark:text-white">{{ $this->assignment->class?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Subject') }}</p>
                <p class="text-gray-900 dark:text-white">{{ $this->assignment->subject?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Due Date') }}</p>
                <p class="text-gray-900 dark:text-white">{{ $this->assignment->due_date?->format('M d, Y') }}</p>
            </div>
        </div>
    </flux:card>

    @if($this->submissions->count())
        <form wire:submit="saveGrades" class="space-y-4">
            <flux:card>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('Submissions') }}
                        <flux:badge variant="info" class="ml-2">{{ $this->submissions->count() }}</flux:badge>
                    </h2>
                    <flux:button type="submit" variant="primary" class="button" size="sm" icon="check">
                        {{ __('Save Grades') }}
                    </flux:button>
                </div>

                <div class="space-y-4">
                    @foreach($this->submissions as $submission)
                        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $submission->student?->user?->first_name }} {{ $submission->student?->user?->last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ __('Submitted') }}: {{ $submission->submitted_at?->format('M d, Y H:i') ?? __('Not submitted') }}
                                    </p>
                                </div>
                                @php
                                    $color = match($submission->status) {
                                        'graded' => 'green', 'submitted' => 'blue', 'late' => 'red', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($submission->status) }}</flux:badge>
                            </div>

                            @if($submission->content)
                                <div class="mb-3 rounded bg-zinc-50 dark:bg-zinc-800 p-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $submission->content }}
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <flux:input
                                    label="{{ __('Marks') }} / {{ $this->assignment->total_marks ?? '?' }}"
                                    type="text"
                                    inputmode="decimal"
                                    wire:model="grades.{{ $submission->id }}.marks_obtained"
                                    placeholder="0"
                                />
                                <flux:input
                                    label="{{ __('Feedback') }}"
                                    wire:model="grades.{{ $submission->id }}.feedback"
                                    placeholder="{{ __('Optional feedback') }}"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </form>
    @else
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Submissions Yet') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Students have not submitted this assignment yet.') }}</p>
            </div>
        </flux:card>
    @endif
</div>
