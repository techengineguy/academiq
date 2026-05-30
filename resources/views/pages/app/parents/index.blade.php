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
        return StudentParent::where('tenant_id', Auth::user()->tenant_id)
            ->with(['user', 'students.user'])
            ->withCount('students')
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    #[Computed]
    public function totalParents(): int
    {
        return (int) StudentParent::where('tenant_id', Auth::user()->tenant_id)->count();
    }

    public function confirmDelete(int $id): void
    {
        $this->parentIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this parent? Their account and child links will be removed.'))
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

        $parent = StudentParent::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->parentIdToDelete);

        // Detach students and delete user account too
        $parent->students()->detach();
        $parent->user?->delete();
        $parent->delete();

        $this->parentIdToDelete = null;
        unset($this->parents);

        Flux::toast(variant: 'success', text: __('Parent deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Parents') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Register and link parents to their children.') }}</p>
        </div>

        <flux:button class="button" href="{{ route('parents.create') }}" wire:navigate icon="plus">
            {{ __('New Parent') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->parents->count())
            <flux:table :paginate="$this->parents">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ __('Children') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->parents as $parent)
                    <flux:table.rows>
                        <flux:table.row :key="$parent->id">
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $parent->user?->first_name }} {{ $parent->user?->last_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $parent->guardian_relation ?? __('Guardian') }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $parent->user?->email ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $parent->father_phone ?? $parent->mother_phone ?? $parent->guardian_phone ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:badge color="blue">{{ $parent->students_count }}</flux:badge>
                                    @if($parent->students->count())
                                        <span class="text-xs text-gray-500 truncate max-w-[200px]">
                                            {{ $parent->students->take(2)->map(fn ($s) => $s->user?->first_name . ' ' . $s->user?->last_name)->join(', ') }}
                                            @if($parent->students->count() > 2)
                                                +{{ $parent->students->count() - 2 }}
                                            @endif
                                        </span>
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
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Register the first parent to grant access to the parent portal.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
