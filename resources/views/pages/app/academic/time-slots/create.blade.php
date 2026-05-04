<?php

use Livewire\Component;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $name = '';
    public $start_time = '';
    public $end_time = '';
    public $is_break = false;
    public $order = 0;

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_break' => 'boolean',
            'order' => 'required|integer|min:0',
        ]);

        $institution = Auth::user()->institution;

        TimeSlot::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_break' => $validated['is_break'],
            'order' => $validated['order'],
        ]);

        Flux::toast(variant: 'success', text: __('Time slot created successfully.'));

        $this->redirect(route('time-slots.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Name') }}" placeholder="{{ __('e.g., Period 1, Morning Session') }}" wire:model="name" required />
        <flux:time-picker time-format="12-hour" label="{{ __('Start Time') }}" wire:model="start_time" required />
        <flux:time-picker time-format="12-hour" label="{{ __('End Time') }}" wire:model="end_time" required />
        <flux:input label="{{ __('Order') }}" placeholder="{{ __('e.g., 0 for first period, 1 for second, etc.') }}" type="number" wire:model.number="order" min="0" required />
        
        <div class="flex items-center">
            <input type="checkbox" id="is_break" wire:model="is_break" class="w-4 h-4 rounded border-gray-300">
            <label for="is_break" class="ms-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('This is a break time') }}
            </label>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-time-slot')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
