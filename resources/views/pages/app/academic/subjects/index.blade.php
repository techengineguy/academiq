<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Subject;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Subjects')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function subjects()
    {
        return Subject::orderBy('name', 'asc')->paginate(10);
    }

    public $subjectIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->subjectIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this subject?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->subjectIdToDelete) return;

        Subject::findOrFail($this->subjectIdToDelete)->delete();

        $this->subjectIdToDelete = null;
        unset($this->subjects);

        Flux::toast(variant: 'success', text: __('Subject deleted successfully, Restore from trash.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Subjects') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage subjects and their associated classes.') }}</p>
        </div>
        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-subject')" icon="plus">
            {{ __('New Subject') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->subjects->count())
            <flux:table :paginate="$this->subjects">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Code') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->subjects as $subject)
                    <flux:table.rows>
                        <flux:table.row :key="$subject->id">
                            <flux:table.cell>{{ $subject->name }}</flux:table.cell>
                            <flux:table.cell>{{ $subject->code }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst($subject->type) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$subject->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($subject->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-subject'), $wire.dispatch('edit-subject', { uuid: '{{ $subject->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $subject->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Subjects') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new subject.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-subject" title="{{ __('Create Subject') }}">
        <livewire:pages::app.academic.subjects.create />
    </x-slide>

    <x-slide id="edit-subject" title="{{ __('Edit Subject') }}">
        <livewire:pages::app.academic.subjects.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>


