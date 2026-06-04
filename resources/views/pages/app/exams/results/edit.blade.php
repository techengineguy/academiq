<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\ExamResult;
use App\Models\GradeScale;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Exam Result')]
class extends Component {

    public ?ExamResult $result = null;

    public string $marks_obtained = '';
    public string $total_marks = '';
    public bool $is_absent = false;
    public string $remarks = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadResult($id);
        }
    }

    #[On('edit-result')]
    public function loadResult(int $id): void
    {
        $this->result = ExamResult::with(['examSchedule.exam', 'examSchedule.subject', 'examSchedule.class', 'student.user'])
            ->findOrFail($id);

        $this->marks_obtained = (string) $this->result->marks_obtained;
        $this->total_marks = (string) $this->result->total_marks;
        $this->is_absent = $this->result->is_absent;
        $this->remarks = (string) ($this->result->remarks ?? '');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'is_absent' => ['boolean'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $isAbsent = (bool) $validated['is_absent'];
        $marksObtained = $isAbsent ? 0 : (float) ($validated['marks_obtained'] ?: 0);
        $totalMarks = (float) $this->total_marks;
        $percentage = $totalMarks > 0 ? ($marksObtained / $totalMarks) * 100 : 0;

        $grade = null;
        if (! $isAbsent) {
            $gradeScales = GradeScale::orderByDesc('min_percentage')
                ->get();

            foreach ($gradeScales as $scale) {
                if ($percentage >= (float) $scale->min_percentage && $percentage <= (float) $scale->max_percentage) {
                    $grade = $scale->grade;
                    break;
                }
            }
        }

        $this->result->update([
            'marks_obtained' => number_format($marksObtained, 2, '.', ''),
            'total_marks' => number_format($totalMarks, 2, '.', ''),
            'grade' => $grade,
            'is_absent' => $isAbsent,
            'remarks' => $validated['remarks'] !== '' ? $validated['remarks'] : null,
            'entered_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: __('Result updated successfully.'));

        $this->redirect(route('results.index'), navigate: true);
    }
};
?>
<div>
    @if($this->result)
        <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-zinc-800">
            <div class="grid gap-2 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Student') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $this->result->student?->user?->first_name }} {{ $this->result->student?->user?->last_name }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Exam') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $this->result->examSchedule?->exam?->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Subject') }}</p>
                    <p class="text-gray-900 dark:text-white">{{ $this->result->examSchedule?->subject?->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Class') }}</p>
                    <p class="text-gray-900 dark:text-white">{{ $this->result->examSchedule?->class?->name }}</p>
                </div>
            </div>
        </div>

        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    label="{{ __('Marks Obtained') }}"
                    type="text"
                    inputmode="decimal"
                    wire:model="marks_obtained"
                    :disabled="$is_absent"
                />
                <flux:input
                    label="{{ __('Total Marks') }}"
                    type="text"
                    inputmode="decimal"
                    value="{{ $total_marks }}"
                    disabled
                />
            </div>

            <flux:checkbox label="{{ __('Mark as Absent') }}" wire:model.live="is_absent" />

            <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-result')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
