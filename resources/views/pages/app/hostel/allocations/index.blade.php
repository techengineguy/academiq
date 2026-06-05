<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\HostelAllocation;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Hostel Allocations')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';

    public ?int $allocationIdToDelete = null;

    #[Computed]
    public function allocations()
    {
        $query = HostelAllocation::with(['student.user', 'hostelRoom.hostelBuilding', 'academicYear'])
            ->orderByDesc('allocated_date');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $base = HostelAllocation::query();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'vacated' => (clone $base)->where('status', 'vacated')->count(),
        ];
    }

    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->allocationIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this allocation?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->allocationIdToDelete) {
            return;
        }

        $allocation = HostelAllocation::findOrFail($this->allocationIdToDelete);

        // Decrement room occupancy if active
        if ($allocation->status === 'active' && $allocation->hostelRoom) {
            $allocation->hostelRoom->decrement('occupied');
        }

        $allocation->delete();

        $this->allocationIdToDelete = null;
        unset($this->allocations);

        Flux::toast(variant: 'success', text: __('Allocation deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Hostel Allocations') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Assign students to hostel rooms.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-allocation')" icon="plus">
            {{ __('New Allocation') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Allocations') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['total']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Active') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->stats['active']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Vacated') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-600 dark:text-gray-400">{{ number_format($this->stats['vacated']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="vacated">{{ __('Vacated') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->allocations->count())
            <flux:table :paginate="$this->allocations">
                <flux:table.columns>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Building') }}</flux:table.column>
                    <flux:table.column>{{ __('Room') }}</flux:table.column>
                    <flux:table.column>{{ __('Bed') }}</flux:table.column>
                    <flux:table.column>{{ __('Allocated') }}</flux:table.column>
                    <flux:table.column>{{ __('Vacated') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->allocations as $allocation)
                    <flux:table.rows>
                        <flux:table.row :key="$allocation->id">
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $allocation->student?->user?->first_name }} {{ $allocation->student?->user?->last_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $allocation->student?->admission_number }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $allocation->hostelRoom?->hostelBuilding?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $allocation->hostelRoom?->room_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $allocation->bed_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $allocation->allocated_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $allocation->vacated_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$allocation->status === 'active' ? 'green' : 'gray'">{{ ucfirst($allocation->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-allocation'), $wire.dispatch('edit-allocation', { id: {{ $allocation->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $allocation->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Allocations') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-allocation" title="{{ __('New Allocation') }}" size="lg">
        <livewire:pages::app.hostel.allocations.create />
    </x-slide>

    <x-slide id="edit-allocation" title="{{ __('Edit Allocation') }}" size="lg">
        <livewire:pages::app.hostel.allocations.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
