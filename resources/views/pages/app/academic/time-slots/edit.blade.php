<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public ?TimeSlot $timeSlot = null;

    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public bool $is_break = false;
    public int $order = 0;

    #[On('edit-time-slot')]
    public function loadTimeSlot(string $uuid): void
    {
        $this->timeSlot = TimeSlot::where('uuid', $uuid)->firstOrFail();

        $this->name = $this->timeSlot->name;
        $this->start_time = $this->timeSlot->start_time ? \Carbon\Carbon::parse($this->timeSlot->start_time)->format('H:i') : '';
        $this->end_time = $this->timeSlot->end_time ? \Carbon\Carbon::parse($this->timeSlot->end_time)->format('H:i') : '';
        $this->is_break = $this->timeSlot->is_break;
        $this->order = $this->timeSlot->order;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break' => ['boolean'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $this->timeSlot->update([
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_break' => $this->is_break,
            'order' => $this->order,
        ]);

        Flux::toast(variant: 'success', text: __('Time slot updated successfully.'));

        $this->redirect(route('time-slots.index'), navigate: true);
    }
};
?>

<div>
    @if($this->timeSlot)
        <form wire:submit="update" class="space-y-6">
            <flux:input
                label="{{ __('Name') }}"
                wire:model="name"
                required
            />
            <flux:input
                label="{{ __('Start Time') }}"
                type="time"
                wire:model="start_time"
                required
            />
            <flux:input
                label="{{ __('End Time') }}"
                type="time"
                wire:model="end_time"
                required
            />
            <flux:input
                label="{{ __('Order') }}"
                placeholder="{{ __('e.g., 0 for first period, 1 for second, etc.') }}"
                type="number"
                wire:model.number="order"
                min="0"
                required
            />
            
            <div class="flex items-center">
                <input type="checkbox" id="is_break" wire:model="is_break" class="w-4 h-4 rounded border-gray-300">
                <label for="is_break" class="ms-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('This is a break time') }}
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-time-slot')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
