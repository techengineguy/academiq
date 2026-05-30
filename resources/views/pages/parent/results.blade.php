<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Concerns\ScopesToParentChildren;
use App\Models\ExamResult;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;

new
#[Title('Children Results')]
#[Layout('layouts.parent')]
class extends Component {
    use ScopesToParentChildren;

    public string $filterChild = '';
    public string $filterExam = '';

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

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
        $childIds = $this->parentChildIds();
        if ($this->filterChild !== '') {
            $childIds = [$this->filterChild];
        }

        $query = ExamResult::whereIn('student_id', $childIds)
            ->with(['examSchedule.exam', 'examSchedule.subject', 'student.user'])
            ->whereHas('examSchedule.exam', fn ($q) => $q->where('result_published', true))
            ->orderByDesc('created_at');

        if ($this->filterExam !== '') {
            $query->whereHas('examSchedule', fn ($q) => $q->where('exam_id', $this->filterExam));
        }

        return $query->get()->groupBy(fn ($r) => $r->student?->user?->first_name . ' - ' . $r->examSchedule?->exam?->name);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Children Results') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View exam results for your children.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:select variant="listbox" wire:model.live="filterChild" placeholder="{{ __('All Children') }}">
            <flux:select.option value="">{{ __('All Children') }}</flux:select.option>
            @foreach($this->children as $child)
                <flux:select.option value="{{ $child->id }}">
                    {{ $child->user?->first_name }} {{ $child->user?->last_name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="filterExam" placeholder="{{ __('All Exams') }}">
            <flux:select.option value="">{{ __('All Exams') }}</flux:select.option>
            @foreach($this->exams as $exam)
                <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @forelse($this->results as $groupName => $groupResults)
        <flux:card>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ $groupName }}</h2>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Marks') }}</flux:table.column>
                    <flux:table.column>{{ __('Grade') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                @foreach($groupResults as $result)
                    <flux:table.rows>
                        <flux:table.row>
                            <flux:table.cell>{{ $result->examSchedule?->subject?->name ?? '-' }}</flux:table.cell>
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
                                @else - @endif
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
        </flux:card>
    @empty
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Results') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Results will appear here once published.') }}</p>
            </div>
        </flux:card>
    @endforelse
</div>
</div>
