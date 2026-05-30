<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Concerns\ScopesToParentChildren;
use App\Models\Timetable;
use Illuminate\Support\Facades\Auth;

new
#[Title('Children Timetable')]
#[Layout('layouts.parent')]
class extends Component {
    use ScopesToParentChildren;

    public string $selected_child = '';

    public function mount(): void
    {
        $first = $this->parentChildren()->first();
        if ($first) {
            $this->selected_child = (string) $first->id;
        }
    }

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

    #[Computed]
    public function timetables()
    {
        if ($this->selected_child === '') {
            return collect();
        }

        $child = $this->parentChildren()->firstWhere('id', (int) $this->selected_child);
        if (! $child) {
            return collect();
        }

        return Timetable::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $child->class_id)
            ->where('section_id', $child->section_id)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get()
            ->groupBy('day');
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Children Timetable') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View weekly schedule for your child.') }}</p>
    </div>

    <flux:select variant="listbox" wire:model.live="selected_child" placeholder="{{ __('Select Child') }}">
        @foreach($this->children as $child)
            <flux:select.option value="{{ $child->id }}">
                {{ $child->user?->first_name }} {{ $child->user?->last_name }} ({{ $child->class?->name }})
            </flux:select.option>
        @endforeach
    </flux:select>

    @forelse($this->timetables as $day => $slots)
        <flux:card>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 capitalize">{{ $day }}</h2>
            <div class="space-y-2">
                @foreach($slots as $slot)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="text-xs font-medium text-zinc-500 w-20">
                                {{ $slot->timeSlot?->start_time?->format('H:i') ?? '-' }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $slot->subject?->name ?? '-' }}</p>
                                <p class="text-xs text-zinc-500">{{ $slot->teacher?->first_name }} {{ $slot->teacher?->last_name }}</p>
                            </div>
                        </div>
                        @if($slot->room)
                            <flux:badge color="gray" size="sm">{{ $slot->room }}</flux:badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </flux:card>
    @empty
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Timetable') }}</h3>
            </div>
        </flux:card>
    @endforelse
</div>
</div>
