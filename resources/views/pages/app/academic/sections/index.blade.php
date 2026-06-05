<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Section;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Sections')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function sections()
    {
        return Section::with('class')
            ->orderBy('name', 'asc')->paginate(10);
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