<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Concerns\ScopesToParentChildren;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;

new
#[Title('Assignments')]
#[Layout('layouts.parent')]
class extends Component {
    use WithPagination, ScopesToParentChildren;

    public string $filterChild = '';

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

    #[Computed]
    public function assignments()
    {
        $children = $this->parentChildren();
        $classIds = $children->pluck('class_id')->unique();

        if ($this->filterChild !== '') {
            $child = $children->firstWhere('id', (int) $this->filterChild);
            $classIds = $child ? collect([$child->class_id]) : collect();
        }

        return Assignment::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('class_id', $classIds)
            ->with(['class', 'subject', 'teacher'])
            ->orderByDesc('due_date')
            ->paginate(15);
    }

    public function updatedFilterChild(): void { $this->resetPage(); }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Assignments') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track your children\'s assignments.') }}</p>
    </div>

    <flux:card>
        <div class="mb-4">
            <flux:select variant="listbox" wire:model.live="filterChild" placeholder="{{ __('All Children') }}">
                <flux:select.option value="">{{ __('All Children') }}</flux:select.option>
                @foreach($this->children as $child)
                    <flux:select.option value="{{ $child->id }}">
                        {{ $child->user?->first_name }} {{ $child->user?->last_name }} ({{ $child->class?->name }})
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        @if($this->assignments->count())
            <flux:table :paginate="$this->assignments">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
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
                                @if($assignment->due_date?->isPast())
                                    <flux:badge color="red">{{ __('Overdue') }}</flux:badge>
                                @else
                                    <flux:badge color="green">{{ __('Active') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Assignments') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
