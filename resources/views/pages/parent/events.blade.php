<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

new
#[Title('Events Calendar')]
#[Layout('layouts.parent')]
class extends Component {
    use WithPagination;

    public string $filterType = '';

    #[Computed]
    public function events()
    {
        $query = Event::whereIn('status', ['upcoming', 'ongoing'])
            ->with('organizer')
            ->orderBy('start_date');

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        return $query->paginate(15);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Events Calendar') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Upcoming events and activities at your school.') }}</p>
    </div>

    <flux:card>
        <div class="mb-4">
            <flux:select variant="listbox" wire:model.live="filterType" placeholder="{{ __('All Types') }}" class="max-w-xs">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="academic">{{ __('Academic') }}</flux:select.option>
                <flux:select.option value="sports">{{ __('Sports') }}</flux:select.option>
                <flux:select.option value="cultural">{{ __('Cultural') }}</flux:select.option>
                <flux:select.option value="meeting">{{ __('Meeting') }}</flux:select.option>
                <flux:select.option value="holiday">{{ __('Holiday') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->events->count())
            <div class="space-y-3">
                @foreach($this->events as $event)
                    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $event->title }}</h3>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="calendar-days" class="size-3" />
                                        {{ $event->start_date?->format('M d') }}@if($event->end_date && $event->end_date->ne($event->start_date)) - {{ $event->end_date->format('M d, Y') }}@else, {{ $event->start_date?->format('Y') }}@endif
                                    </span>
                                    @if($event->start_time)
                                        <span class="flex items-center gap-1">
                                            <flux:icon name="clock" class="size-3" />
                                            {{ $event->start_time->format('H:i') }}@if($event->end_time) - {{ $event->end_time->format('H:i') }}@endif
                                        </span>
                                    @endif
                                    @if($event->venue)
                                        <span class="flex items-center gap-1">
                                            <flux:icon name="map-pin" class="size-3" />
                                            {{ $event->venue }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <flux:badge :color="$event->status === 'upcoming' ? 'blue' : 'yellow'">{{ ucfirst($event->status) }}</flux:badge>
                        </div>
                        @if($event->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $event->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $this->events->links() }}</div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="calendar" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Events') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
