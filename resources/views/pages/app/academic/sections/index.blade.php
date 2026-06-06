<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Section;
use App\Models\ClassModel;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Sections')] 
class extends Component {
    use WithPagination;
    use Interactions;

    public string $search = '';

    #[Computed]
    public function hasClasses(): bool
    {
        return ClassModel::exists();
    }

    #[Computed]
    public function sections()
    {
        return Section::with('class')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhereHas('class', function ($q2) {
                          $q2->where('name', 'like', "%{$this->search}%");
                      });
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public $sectionIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->sectionIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this section?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->sectionIdToDelete) return;

        Section::findOrFail($this->sectionIdToDelete)->delete();

        $this->sectionIdToDelete = null;
        unset($this->sections);

        Flux::toast(variant: 'success', text: __('Section deleted successfully, Restore from trash.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>

    @if(!$this->hasClasses)
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-start gap-3">
                <flux:icon name="information-circle" class="h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ __('Classes Required') }}</h3>
                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                        {{ __('You need to create at least one class before creating sections. Sections are used to divide classes into smaller groups.') }}
                    </p>
                    <flux:button href="{{ route('classes.index') }}" wire:navigate variant="primary" size="sm" class="mt-3">
                        {{ __('Go to Classes') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Sections') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage sections and their associated classes.') }}</p>
        </div>
        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-section')" icon="plus">
            {{ __('New Section') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="{{ __('Search by section name or class...') }}" 
                icon="magnifying-glass"
            />
        </div>

        @if($this->sections->count())
            <flux:table :paginate="$this->sections">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Capacity') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->sections as $section)
                    <flux:table.rows>
                        <flux:table.row :key="$section->id">
                            <flux:table.cell>{{ $section->name }}</flux:table.cell>
                            <flux:table.cell>{{ $section->class?->name }}</flux:table.cell>
                            <flux:table.cell>{{ $section->capacity }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$section->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($section->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-section'), $wire.dispatch('edit-section', { uuid: '{{ $section->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $section->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Sections') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new section.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-section" title="{{ __('Create Section') }}">
        <livewire:pages::app.academic.sections.create />
    </x-slide>

    <x-slide id="edit-section" title="{{ __('Edit Section') }}">
        <livewire:pages::app.academic.sections.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>