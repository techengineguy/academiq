<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\HostelRoom;
use App\Models\HostelBuilding;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Hostel Rooms')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterBuilding = '';
    public string $filterStatus = '';

    public ?int $roomIdToDelete = null;

    #[Computed]
    public function rooms()
    {
        $query = HostelRoom::whereHas('hostelBuilding', fn ($q) => $q)
            ->with('hostelBuilding')
            ->orderBy('hostel_building_id')
            ->orderBy('room_number');

        if ($this->filterBuilding !== '') {
            $query->where('hostel_building_id', $this->filterBuilding);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function buildings()
    {
        return HostelBuilding::orderBy('name')
            ->get();
    }

    public function updatedFilterBuilding(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterBuilding = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->roomIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this room?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->roomIdToDelete) {
            return;
        }

        HostelRoom::whereHas('hostelBuilding', fn ($q) => $q)
            ->findOrFail($this->roomIdToDelete)
            ->delete();

        $this->roomIdToDelete = null;
        unset($this->rooms);

        Flux::toast(variant: 'success', text: __('Room deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Hostel Rooms') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage hostel rooms and their occupancy.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-room')" icon="plus">
            {{ __('New Room') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterBuilding" placeholder="{{ __('All Buildings') }}">
                <flux:select.option value="">{{ __('All Buildings') }}</flux:select.option>
                @foreach($this->buildings as $building)
                    <flux:select.option value="{{ $building->id }}">{{ $building->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="available">{{ __('Available') }}</flux:select.option>
                <flux:select.option value="occupied">{{ __('Occupied') }}</flux:select.option>
                <flux:select.option value="maintenance">{{ __('Maintenance') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->rooms->count())
            <flux:table :paginate="$this->rooms">
                <flux:table.columns>
                    <flux:table.column>{{ __('Room') }}</flux:table.column>
                    <flux:table.column>{{ __('Building') }}</flux:table.column>
                    <flux:table.column>{{ __('Floor') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Capacity') }}</flux:table.column>
                    <flux:table.column>{{ __('Occupied') }}</flux:table.column>
                    <flux:table.column>{{ __('Rent') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->rooms as $room)
                    <flux:table.rows>
                        <flux:table.row :key="$room->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $room->room_number }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $room->hostelBuilding?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $room->floor ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $room->room_type ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $room->capacity }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="{{ $room->occupied >= $room->capacity ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $room->occupied ?? 0 }} / {{ $room->capacity }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $room->rent_amount, 2) }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($room->status) {
                                        'available' => 'green', 'occupied' => 'blue', 'maintenance' => 'yellow', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($room->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-room'), $wire.dispatch('edit-room', { id: {{ $room->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $room->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Rooms') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-room" title="{{ __('Create Room') }}" size="lg">
        <livewire:pages::app.hostel.rooms.create />
    </x-slide>

    <x-slide id="edit-room" title="{{ __('Edit Room') }}" size="lg">
        <livewire:pages::app.hostel.rooms.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
