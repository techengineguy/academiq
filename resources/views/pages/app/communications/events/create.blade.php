<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Event')]
class extends Component {

    public string $title = '';
    public string $description = '';
    public string $type = 'academic';
    public string $start_date = '';
    public string $end_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $venue = '';
    public bool $requires_rsvp = false;
    public string $status = 'upcoming';

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:academic,sports,cultural,meeting,holiday,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'venue' => ['nullable', 'string', 'max:255'],
            'requires_rsvp' => ['boolean'],
            'status' => ['required', 'in:upcoming,ongoing,completed,cancelled'],
        ]);

        Event::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'organized_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'start_time' => $validated['start_time'] ?: null,
            'end_time' => $validated['end_time'] ?: null,
            'venue' => $validated['venue'] ?: null,
            'requires_rsvp' => $validated['requires_rsvp'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Event created successfully.'));

        $this->redirect(route('events.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Title') }}" wire:model="title" placeholder="{{ __('e.g., Annual Sports Day') }}" required />

        <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="3" />

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
                <flux:select.option value="academic">{{ __('Academic') }}</flux:select.option>
                <flux:select.option value="sports">{{ __('Sports') }}</flux:select.option>
                <flux:select.option value="cultural">{{ __('Cultural') }}</flux:select.option>
                <flux:select.option value="meeting">{{ __('Meeting') }}</flux:select.option>
                <flux:select.option value="holiday">{{ __('Holiday') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="upcoming">{{ __('Upcoming') }}</flux:select.option>
                <flux:select.option value="ongoing">{{ __('Ongoing') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Start Date') }}" wire:model="start_date" required />
            <flux:date-picker label="{{ __('End Date') }}" wire:model="end_date" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:time-picker label="{{ __('Start Time') }}" wire:model="start_time" />
            <flux:time-picker label="{{ __('End Time') }}" wire:model="end_time" />
        </div>

        <flux:input label="{{ __('Venue') }}" wire:model="venue" placeholder="{{ __('e.g., School Auditorium') }}" />

        <flux:checkbox label="{{ __('Requires RSVP') }}" wire:model="requires_rsvp" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-event')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
