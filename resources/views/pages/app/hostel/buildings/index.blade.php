<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\HostelBuilding;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Hostel Buildings')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $buildingIdToDelete = null;

    #[Computed]
    public function buildings()
    {
        return HostelBuilding::where('tenant_id', Auth::user()->tenant_id)
            ->with('warden')
            ->withCount('rooms')
            ->orderBy('name')
            ->paginate(15);
    }

    public function confirmDelete(int $id): void
    {
        $this->buildingIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this building? All rooms and allocations will be affected.'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->buildingIdToDelete) {
            return;
        }

        HostelBuilding::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->buildingIdToDelete)
            ->delete();

        $this->buildingIdToDelete = null;
        unset($this->buildings);

        Flux::toast(variant: 'success', text: __('Building deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Hostel Buildings') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage hostel buildings and assign wardens.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-building')" icon="plus">
            {{ __('New Building') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->buildings->count())
            <flux:table :paginate="$this->buildings">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Code') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Floors') }}</flux:table.column>
                    <flux:table.column>{{ __('Rooms') }}</flux:table.column>
                    <flux:table.column>{{ __('Warden') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->buildings as $building)
                    <flux:table.rows>
                        <flux:table.row :key="$building->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $building->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell><code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $building->code }}</code></flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$building->type === 'boys' ? 'blue' : ($building->type === 'girls' ? 'pink' : 'gray')">
                                    {{ ucfirst($building->type ?? '-') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $building->total_floors ?? '-' }}</flux:table.cell>
                            <flux:table.cell><flux:badge color="blue">{{ $building->rooms_count }}</flux:badge></flux:table.cell>
                            <flux:table.cell>
                                {{ $building->warden ? $building->warden->first_name . ' ' . $building->warden->last_name : '-' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$building->status === 'active' ? 'green' : 'gray'">{{ ucfirst($building->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-building'), $wire.dispatch('edit-building', { id: {{ $building->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $building->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Buildings') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Add your first hostel building to get started.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-building" title="{{ __('Create Building') }}" size="lg">
        <livewire:pages::app.hostel.buildings.create />
    </x-slide>

    <x-slide id="edit-building" title="{{ __('Edit Building') }}" size="lg">
        <livewire:pages::app.hostel.buildings.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
