<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\TimeSlot;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new #[Title('Time Slots')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function timeSlots()
    {
        return TimeSlot::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('order', 'asc')->paginate(10);
    }

    public $timeSlotIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->timeSlotIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this time slot?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->timeSlotIdToDelete) return;

        TimeSlot::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->timeSlotIdToDelete)->delete();

        $this->timeSlotIdToDelete = null;
        unset($this->timeSlots);

        Flux::toast(variant: 'success', text: __('Time slot deleted successfully, Restore from trash.'));
    }
};
?>

<div class="py-4">
    <x-dialog/>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Time Slots') }}</h1>
            <flux:button class="button" x-on:click="$tsui.open.slide('create-time-slot')" icon="plus">
                {{ __('New Time Slot') }}
            </flux:button>
        </div>

        <flux:card>
            @if($this->timeSlots->count())
                <flux:table :paginate="$this->timeSlots">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column>{{ __('Start Time') }}</flux:table.column>
                        <flux:table.column>{{ __('End Time') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Order') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach($this->timeSlots as $slot)
                        <flux:table.rows>
                            <flux:table.row :key="$slot->id">
                                <flux:table.cell>{{ $slot->name }}</flux:table.cell>
                                <flux:table.cell>{{ $slot->start_time ? Carbon::parse($slot->start_time)->format('H:i') : '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $slot->end_time ? Carbon::parse($slot->end_time)->format('H:i') : '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$slot->is_break ? 'orange' : 'blue'">
                                        {{ $slot->is_break ? __('Break') : __('Class') }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $slot->order }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2">
                                        <flux:button 
                                            size="sm" 
                                            variant="subtle" 
                                            x-on:click="$tsui.open.slide('edit-time-slot'), $wire.dispatch('edit-time-slot', { uuid: '{{ $slot->uuid }}' })" 
                                            icon="pencil" 
                                        />
                                        <flux:button 
                                            size="sm" 
                                            variant="danger" 
                                            icon="trash"
                                            wire:click="confirmDelete({{ $slot->id }})"
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
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Time Slots') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new time slot.') }}</p>
                </div>
            @endif
        </flux:card>
    </div>

    <x-slide id="create-time-slot" title="{{ __('Create Time Slot') }}">
        <livewire:pages::app.academic.time-slots.create />
    </x-slide>

    <x-slide id="edit-time-slot" title="{{ __('Edit Time Slot') }}">
        <livewire:pages::app.academic.time-slots.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>
