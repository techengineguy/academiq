<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Events')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';
    public string $filterType = '';

    public ?int $eventIdToDelete = null;

    #[Computed]
    public function events()
    {
        $query = Event::with('organizer')
            ->withCount('participants')
            ->orderByDesc('start_date');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        return $query->paginate(15);
    }

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->filterType = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->eventIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this event?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->eventIdToDelete) {
            return;
        }

        Event::findOrFail($this->eventIdToDelete)
            ->delete();

        $this->eventIdToDelete = null;
        unset($this->events);

        Flux::toast(variant: 'success', text: __('Event deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Events') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Schedule and manage school events.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-event')" icon="plus">
            {{ __('New Event') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterType" placeholder="{{ __('All Types') }}">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="academic">{{ __('Academic') }}</flux:select.option>
                <flux:select.option value="sports">{{ __('Sports') }}</flux:select.option>
                <flux:select.option value="cultural">{{ __('Cultural') }}</flux:select.option>
                <flux:select.option value="meeting">{{ __('Meeting') }}</flux:select.option>
                <flux:select.option value="holiday">{{ __('Holiday') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="upcoming">{{ __('Upcoming') }}</flux:select.option>
                <flux:select.option value="ongoing">{{ __('Ongoing') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->events->count())
            <flux:table :paginate="$this->events">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Time') }}</flux:table.column>
                    <flux:table.column>{{ __('Venue') }}</flux:table.column>
                    <flux:table.column>{{ __('Participants') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->events as $event)
                    <flux:table.rows>
                        <flux:table.row :key="$event->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst($event->type ?? '-') }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $event->start_date?->format('M d') }}
                                @if($event->end_date && $event->end_date->ne($event->start_date))
                                    - {{ $event->end_date?->format('M d, Y') }}
                                @else
                                    {{ $event->start_date?->format(', Y') }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $event->start_time?->format('H:i') }}
                                @if($event->end_time)
                                    - {{ $event->end_time->format('H:i') }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $event->venue ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green">{{ $event->participants_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($event->status) {
                                        'upcoming' => 'blue', 'ongoing' => 'yellow', 'completed' => 'green', 'cancelled' => 'red', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($event->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-event'), $wire.dispatch('edit-event', { id: {{ $event->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $event->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Events') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-event" title="{{ __('Create Event') }}" size="xl">
        <livewire:pages::app.communications.events.create />
    </x-slide>

    <x-slide id="edit-event" title="{{ __('Edit Event') }}" size="xl">
        <livewire:pages::app.communications.events.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
