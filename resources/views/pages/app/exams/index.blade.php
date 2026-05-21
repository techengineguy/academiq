<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Exam;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Exams')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';
    public string $filterType = '';
    public string $filterAcademicYear = '';

    public ?int $examIdToDelete = null;

    #[Computed]
    public function exams()
    {
        $query = Exam::where('tenant_id', Auth::user()->tenant_id)
            ->with(['academicYear'])
            ->orderByDesc('start_date');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterAcademicYear !== '') {
            $query->where('academic_year_id', $this->filterAcademicYear);
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function totalExams(): int
    {
        return (int) Exam::where('tenant_id', Auth::user()->tenant_id)->count();
    }

    #[Computed]
    public function scheduledCount(): int
    {
        return (int) Exam::where('tenant_id', Auth::user()->tenant_id)->where('status', 'scheduled')->count();
    }

    #[Computed]
    public function ongoingCount(): int
    {
        return (int) Exam::where('tenant_id', Auth::user()->tenant_id)->where('status', 'ongoing')->count();
    }

    #[Computed]
    public function completedCount(): int
    {
        return (int) Exam::where('tenant_id', Auth::user()->tenant_id)->where('status', 'completed')->count();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAcademicYear(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->filterType = '';
        $this->filterAcademicYear = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->examIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this exam?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->examIdToDelete) {
            return;
        }

        Exam::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->examIdToDelete)
            ->delete();

        $this->examIdToDelete = null;
        unset($this->exams);

        Flux::toast(variant: 'success', text: __('Exam deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Exams') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage exams, schedules, and results.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-exam')" icon="plus">
            {{ __('New Exam') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Exams') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalExams) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Scheduled') }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($this->scheduledCount) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Ongoing') }}</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->ongoingCount) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Completed') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->completedCount) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterAcademicYear" placeholder="{{ __('All Academic Years') }}">
                <flux:select.option value="">{{ __('All Academic Years') }}</flux:select.option>
                @foreach($this->academicYears as $year)
                    <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterType" placeholder="{{ __('All Types') }}">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="mid_term">{{ __('Mid Term') }}</flux:select.option>
                <flux:select.option value="final">{{ __('Final') }}</flux:select.option>
                <flux:select.option value="unit_test">{{ __('Unit Test') }}</flux:select.option>
                <flux:select.option value="practical">{{ __('Practical') }}</flux:select.option>
                <flux:select.option value="assignment">{{ __('Assignment') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="scheduled">{{ __('Scheduled') }}</flux:select.option>
                <flux:select.option value="ongoing">{{ __('Ongoing') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->exams->count())
            <flux:table :paginate="$this->exams">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Academic Year') }}</flux:table.column>
                    <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                    <flux:table.column>{{ __('End Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Published') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->exams as $exam)
                    <flux:table.rows>
                        <flux:table.row :key="$exam->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $exam->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</flux:table.cell>
                            <flux:table.cell>{{ $exam->academicYear?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $exam->start_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $exam->end_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColor = match($exam->status) {
                                        'scheduled' => 'blue',
                                        'ongoing' => 'yellow',
                                        'completed' => 'green',
                                        'cancelled' => 'red',
                                        default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$statusColor">
                                    {{ ucfirst($exam->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$exam->result_published ? 'green' : 'gray'">
                                    {{ $exam->result_published ? __('Yes') : __('No') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-exam'), $wire.dispatch('edit-exam', { id: {{ $exam->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $exam->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Exams') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new exam.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-exam" title="{{ __('Create Exam') }}" size="xl">
        <livewire:pages::app.exams.create />
    </x-slide>

    <x-slide id="edit-exam" title="{{ __('Edit Exam') }}" size="xl">
        <livewire:pages::app.exams.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
