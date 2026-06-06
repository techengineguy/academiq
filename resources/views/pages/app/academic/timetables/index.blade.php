<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Timetables')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function timetables()
    {
        return Timetable::with(['class', 'subject', 'teacher', 'timeSlot'])
            ->orderBy('day', 'asc')
            ->paginate(10);
    }

    #[Computed]
    public function hasTimeSlots(): bool
    {
        return TimeSlot::exists();
    }

    public $timetableIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->timetableIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this timetable entry?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->timetableIdToDelete) return;

        Timetable::findOrFail($this->timetableIdToDelete)->delete();

        $this->timetableIdToDelete = null;
        unset($this->timetables);

        Flux::toast(variant: 'success', text: __('Timetable entry deleted successfully.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Timetables') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage your timetable entries.') }}</p>
        </div>
        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-timetable')" icon="plus">
            {{ __('New Timetable') }}
        </flux:button>
    </div>

    @if(!$this->hasTimeSlots)
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-700/50 dark:bg-blue-900/20">
            <div class="flex items-start gap-3">
                <flux:icon name="information-circle" class="h-5 w-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200">
                        {{ __('Time Slots Required') }}
                    </h3>
                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                        {{ __('Before creating timetables, you need to create time slots first. Time slots define the periods during which classes are held.') }}
                    </p>
                    <div class="mt-3">
                        <flux:button 
                            href="{{ route('time-slots.index') }}" 
                            wire:navigate
                            size="sm" 
                            variant="primary"
                            icon="clock"
                        >
                            {{ __('Go to Time Slots') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <flux:card>
        @if($this->timetables->count())
            <flux:table :paginate="$this->timetables">
                <flux:table.columns>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                    <flux:table.column>{{ __('Day') }}</flux:table.column>
                    <flux:table.column>{{ __('Time') }}</flux:table.column>
                    <flux:table.column>{{ __('Room') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->timetables as $timetable)
                    <flux:table.rows>
                        <flux:table.row :key="$timetable->id">
                            <flux:table.cell>{{ $timetable->class?->name }}</flux:table.cell>
                            <flux:table.cell>{{ $timetable->subject?->name }}</flux:table.cell>
                            <flux:table.cell>{{ $timetable->teacher?->first_name }} {{ $timetable->teacher?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst($timetable->day) }}</flux:table.cell>
                            <flux:table.cell>
                                @if($timetable->timeSlot)
                                    {{ $timetable->timeSlot->start_time->format('H:i') }} - {{ $timetable->timeSlot->end_time->format('H:i') }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $timetable->room }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-timetable'), $wire.dispatch('edit-timetable', { uuid: '{{ $timetable->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $timetable->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Timetables') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new timetable.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-timetable" title="{{ __('Create Timetable') }}">
        <livewire:pages::app.academic.timetables.create />
    </x-slide>

    <x-slide id="edit-timetable" title="{{ __('Edit Timetable') }}">
        <livewire:pages::app.academic.timetables.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>


