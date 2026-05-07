<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\AcademicYear;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Academic Years')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderBy('start_date', 'desc')->paginate(10);
    }

    public $yearIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->yearIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this academic year?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->yearIdToDelete) return;

        AcademicYear::findOrFail($this->yearIdToDelete)->delete();

        $this->yearIdToDelete = null;
        unset($this->academicYears);

        Flux::toast(variant: 'success', text: __('Academic year deleted successfully, Restore from trash.'));
    }
};
?>
<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Academic Years') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage academic years and their details.') }}</p>
        </div>
        <flux:button class="button" x-on:click="$tsui.open.slide('create-academic-year')" icon="plus">
            {{ __('New Academic Year') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->academicYears->count())
            <flux:table :paginate="$this->academicYears">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:column>
                    <flux:table.column>{{ __('Start Date') }}</flux:column>
                    <flux:table.column>{{ __('End Date') }}</flux:column>
                    <flux:table.column>{{ __('Status') }}</flux:column>
                    <flux:table.column>{{ __('Current') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->academicYears as $year)
                    <flux:table.rows>
                        <flux:table.row :key="$year->id">
                            <flux:table.cell>{{ $year->name }}</flux:table.cell>
                            <flux:table.cell>{{ $year->start_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $year->end_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$year->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($year->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($year->is_current)
                                    <flux:badge variant="info">{{ __('Yes') }}</flux:badge>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" 
                                    x-on:click="$tsui.open.slide('edit-academic-year'); $wire.dispatch('edit-academic-year', { uuid: '{{ $year->uuid }}' })" 
                                    icon="square-pen" />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $year->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Academic Years') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new academic year.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-academic-year" title="{{ __('Create Academic Year') }}">
        <livewire:pages::app.academic.academic-years.create />
    </x-slide>

    <x-slide id="edit-academic-year" title="{{ __('Edit Academic Year') }}">
        <livewire:pages::app.academic.academic-years.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>

