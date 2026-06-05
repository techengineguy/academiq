<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\StudentParent;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Parents')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $parentIdToDelete = null;

    #[Computed]
    public function parents()
    {
        return StudentParent::with(['user', 'students.user'])
            ->withCount('students')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function totalParents(): int
    {
        return (int) StudentParent::count();
    }

    public function confirmDelete(int $id): void
    {
        $this->parentIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this parent? Their user account will also be removed.'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->parentIdToDelete) {
            return;
        }

        $parent = StudentParent::findOrFail($this->parentIdToDelete);

        // Delete the linked user
        $parent->user?->delete();

        // Detach students
        $parent->students()->detach();

        // Soft delete the parent record
        $parent->delete();

        $this->parentIdToDelete = null;
        unset($this->parents);

        Flux::toast(variant: 'success', text: __('Parent deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Parents') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage parent records and link them to students.') }}</p>
        </div>

        <flux:button class="button" href="{{ route('parents.create') }}" wire:navigate icon="plus">
            {{ __('New Parent') }}
        </flux:button>
    </div>

    <flux:card>
        <p class="text-sm text-gray-500">{{ __('Total Parents') }}</p>
        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalParents) }}</p>
    </flux:card>

    <flux:card>
        @if($this->parents->count())
            <flux:table :paginate="$this->parents">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ __('Children') }}</flux:table.column>
                    <flux:table.column>{{ __('Father / Mother / Guardian') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->parents as $parent)
                    <flux:table.rows>
                        <flux:table.row :key="$parent->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar :name="($parent->user?->first_name ?? '') . ' ' . ($parent->user?->last_name ?? '')" size="sm" />
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $parent->user?->first_name }} {{ $parent->user?->last_name }}
                                    </span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $parent->user?->email ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $parent->user?->phone ?? $parent->father_phone ?? $parent->mother_phone ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($parent->students_count)
                                    <flux:badge color="blue">{{ $parent->students_count }}</flux:badge>
                                @else
                                    <flux:badge color="gray">0</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col text-xs">
                                    @if($parent->father_name)
                                        <span><strong>{{ __('F') }}:</strong> {{ $parent->father_name }}</span>
                                    @endif
                                    @if($parent->mother_name)
                                        <span><strong>{{ __('M') }}:</strong> {{ $parent->mother_name }}</span>
                                    @endif
                                    @if($parent->guardian_name)
                                        <span><strong>{{ __('G') }}:</strong> {{ $parent->guardian_name }}</span>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" href="{{ route('parents.edit', $parent->id) }}" wire:navigate />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $parent->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Parents') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Register the first parent to link them with students.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
