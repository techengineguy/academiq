<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\ExamSchedule;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Exam Schedules')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterExam = '';

    public ?int $scheduleIdToDelete = null;

    #[Computed]
    public function schedules()
    {
        $query = ExamSchedule::where('tenant_id', Auth::user()->tenant_id)
            ->with(['exam', 'class', 'subject'])
            ->orderByDesc('exam_date');

        if ($this->filterExam !== '') {
            $query->where('exam_id', $this->filterExam);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function exams()
    {
        return Exam::where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function totalSchedules(): int
    {
        return (int) ExamSchedule::where('tenant_id', Auth::user()->tenant_id)->count();
    }

    public function updatedFilterExam(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterExam = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->scheduleIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this schedule?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->scheduleIdToDelete) {
            return;
        }

        ExamSchedule::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->scheduleIdToDelete)
            ->delete();

        $this->scheduleIdToDelete = null;
        unset($this->schedules);

        Flux::toast(variant: 'success', text: __('Schedule deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Exam Schedules') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage exam schedules with subjects, dates, and rooms.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-schedule')" icon="plus">
            {{ __('New Schedule') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterExam" placeholder="{{ __('All Exams') }}">
                <flux:select.option value="">{{ __('All Exams') }}</flux:select.option>
                @foreach($this->exams as $exam)
                    <flux:select.option value="{{ $exam->id }}">{{ $exam->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->schedules->count())
            <flux:table :paginate="$this->schedules">
                <flux:table.columns>
                    <flux:table.column>{{ __('Exam') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Time') }}</flux:table.column>
                    <flux:table.column>{{ __('Room') }}</flux:table.column>
                    <flux:table.column>{{ __('Total Marks') }}</flux:table.column>
                    <flux:table.column>{{ __('Pass Marks') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->schedules as $schedule)
                    <flux:table.rows>
                        <flux:table.row :key="$schedule->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $schedule->exam?->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $schedule->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $schedule->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $schedule->exam_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $schedule->start_time?->format('H:i') }} - {{ $schedule->end_time?->format('H:i') }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $schedule->room ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $schedule->total_marks }}</flux:table.cell>
                            <flux:table.cell>{{ $schedule->passing_marks }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-schedule'), $wire.dispatch('edit-schedule', { id: {{ $schedule->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $schedule->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Schedules') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create a schedule to assign subjects and dates to an exam.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-schedule" title="{{ __('Create Exam Schedule') }}" size="xl">
        <livewire:pages::app.exams.schedules.create />
    </x-slide>

    <x-slide id="edit-schedule" title="{{ __('Edit Exam Schedule') }}" size="xl">
        <livewire:pages::app.exams.schedules.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
