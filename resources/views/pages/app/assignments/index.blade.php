<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Assignment;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Assignments')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterClass = '';
    public string $filterStatus = '';

    public ?int $assignmentIdToDelete = null;

    #[Computed]
    public function assignments()
    {
        $query = Assignment::with(['class', 'section', 'subject', 'teacher'])
            ->withCount('submissions')
            ->orderByDesc('created_at');

        if ($this->filterClass !== '') {
            $query->where('class_id', $this->filterClass);
        }

        if ($this->filterStatus !== '') {
            if ($this->filterStatus === 'active') {
                $query->where('due_date', '>=', now());
            } elseif ($this->filterStatus === 'closed') {
                $query->where('due_date', '<', now());
            }
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function classes()
    {
        return ClassModel::whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function totalAssignments(): int
    {
        return (int) Assignment::count();
    }

    #[Computed]
    public function activeAssignments(): int
    {
        return (int) Assignment::where('due_date', '>=', now())
            ->count();
    }

    public function updatedFilterClass(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterClass = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->assignmentIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this assignment? All submissions will also be deleted.'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->assignmentIdToDelete) {
            return;
        }

        Assignment::findOrFail($this->assignmentIdToDelete)
            ->delete();

        $this->assignmentIdToDelete = null;
        unset($this->assignments);

        Flux::toast(variant: 'success', text: __('Assignment deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Assignments') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage assignments across all classes.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-assignment')" icon="plus">
            {{ __('New Assignment') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Assignments') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalAssignments) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Active') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->activeAssignments) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterClass" placeholder="{{ __('All Classes') }}">
                <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                @foreach($this->classes as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->assignments->count())
            <flux:table :paginate="$this->assignments">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Submissions') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->assignments as $assignment)
                    <flux:table.rows>
                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $assignment->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->teacher?->first_name }} {{ $assignment->teacher?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->due_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="subtle" :href="route('submissions.show', $assignment->id)" wire:navigate>
                                    <flux:badge color="blue">{{ $assignment->submissions_count }}</flux:badge>
                                </flux:button>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($assignment->due_date?->isPast())
                                    <flux:badge color="red">{{ __('Closed') }}</flux:badge>
                                @else
                                    <flux:badge color="green">{{ __('Active') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-assignment'), $wire.dispatch('edit-assignment', { id: {{ $assignment->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $assignment->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Assignments') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create the first assignment.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-assignment" title="{{ __('Create Assignment') }}" size="xl">
        <livewire:pages::app.assignments.create />
    </x-slide>

    <x-slide id="edit-assignment" title="{{ __('Edit Assignment') }}" size="xl">
        <livewire:pages::app.assignments.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
