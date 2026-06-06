<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\ClassModel;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Classes')] 
class extends Component {
    use WithPagination;
    use Interactions;

    public string $search = '';

    #[Computed]
    public function classes()
    {
        return ClassModel::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public $classIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->classIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this class?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->classIdToDelete) return;

        ClassModel::findOrFail($this->classIdToDelete)->delete();

        $this->classIdToDelete = null;
        unset($this->classes);

        Flux::toast(variant: 'success', text: __('Class deleted successfully, Restore from trash.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Classes') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage classes and their associated sections.') }}</p>
        </div>
        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-class')" icon="plus">
            {{ __('New Class') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="{{ __('Search by name or code...') }}" 
                icon="magnifying-glass"
            />
        </div>

        @if($this->classes->count())
            <flux:table :paginate="$this->classes">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Code') }}</flux:table.column>
                    <flux:table.column>{{ __('Capacity') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->classes as $class)
                    <flux:table.rows>
                        <flux:table.row :key="$class->id">
                            <flux:table.cell>{{ $class->name }}</flux:table.cell>
                            <flux:table.cell>{{ $class->code }}</flux:table.cell>
                            <flux:table.cell>{{ $class->capacity }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$class->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($class->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-class'), $wire.dispatch('edit-class', { uuid: '{{ $class->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $class->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Classes') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new class.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-class" title="{{ __('Create Class') }}">
        <livewire:pages::app.academic.classes.create />
    </x-slide>

    <x-slide id="edit-class" title="{{ __('Edit Class') }}">
        <livewire:pages::app.academic.classes.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>


