<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

new #[Title('Activity Logs')]
class extends Component {
    use WithPagination;

    public string $filterCauser = '';
    public string $filterEvent = '';
    public string $filterDate = '';
    public string $search = '';

    #[Computed]
    public function logs()
    {
        $query = Activity::with('causer')
            ->where(function ($q) {
                // Scope to current tenant's users
                $tenantUserIds = \App\Models\User::pluck('id');
                $q->whereIn('causer_id', $tenantUserIds)
                    ->orWhereNull('causer_id');
            })
            ->orderByDesc('created_at');

        if ($this->filterCauser !== '') {
            $query->where('causer_id', $this->filterCauser);
        }

        if ($this->filterEvent !== '') {
            $query->where('event', $this->filterEvent);
        }

        if ($this->filterDate !== '') {
            $query->whereDate('created_at', $this->filterDate);
        }

        if ($this->search !== '') {
            $query->where('description', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(20);
    }

    #[Computed]
    public function users()
    {
        return \App\Models\User::orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function totalLogs(): int
    {
        $tenantUserIds = \App\Models\User::pluck('id');

        return (int) Activity::whereIn('causer_id', $tenantUserIds)->count();
    }

    public function updatedFilterCauser(): void { $this->resetPage(); }
    public function updatedFilterEvent(): void { $this->resetPage(); }
    public function updatedFilterDate(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterCauser = '';
        $this->filterEvent = '';
        $this->filterDate = '';
        $this->search = '';
        $this->resetPage();
    }
};
?>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Activity Logs') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track all actions performed in the system.') }}</p>
        </div>
        <flux:card class="py-2 px-4">
            <p class="text-xs text-gray-500">{{ __('Total Logs') }}</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalLogs) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search description...') }}"
                icon="magnifying-glass"
            />

            <flux:select variant="listbox" wire:model.live="filterCauser" placeholder="{{ __('All Users') }}" searchable>
                <flux:select.option value="">{{ __('All Users') }}</flux:select.option>
                @foreach($this->users as $user)
                    <flux:select.option value="{{ $user->id }}">
                        {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterEvent" placeholder="{{ __('All Events') }}">
                <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
                <flux:select.option value="created">{{ __('Created') }}</flux:select.option>
                <flux:select.option value="updated">{{ __('Updated') }}</flux:select.option>
                <flux:select.option value="deleted">{{ __('Deleted') }}</flux:select.option>
            </flux:select>

            <flux:date-picker wire:model.live="filterDate" />

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->logs->count())
            <div class="space-y-2">
                @foreach($this->logs as $log)
                    @php
                        $eventColor = match($log->event) {
                            'created' => 'green',
                            'updated' => 'blue',
                            'deleted' => 'red',
                            default => 'gray',
                        };
                        $eventIcon = match($log->event) {
                            'created' => 'plus-circle',
                            'updated' => 'pencil-square',
                            'deleted' => 'trash',
                            default => 'information-circle',
                        };
                    @endphp
                    <div class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div class="p-2 rounded-lg bg-{{ $eventColor }}-100 dark:bg-{{ $eventColor }}-900/30 text-{{ $eventColor }}-600 dark:text-{{ $eventColor }}-400 shrink-0">
                            <flux:icon :name="$eventIcon" class="size-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->description }}</p>
                                @if($log->event)
                                    <flux:badge :color="$eventColor" size="sm">{{ ucfirst($log->event) }}</flux:badge>
                                @endif
                                @if($log->subject_type)
                                    <flux:badge color="gray" size="sm">{{ class_basename($log->subject_type) }}</flux:badge>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                @if($log->causer)
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="user" class="size-3" />
                                        {{ $log->causer->first_name }} {{ $log->causer->last_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">{{ __('System') }}</span>
                                @endif
                                <span>{{ $log->created_at?->format('M d, Y H:i:s') }}</span>
                                <span class="text-gray-400">{{ $log->created_at?->diffForHumans() }}</span>
                            </div>
                            @if($log->properties && $log->properties->isNotEmpty())
                                @php
                                    $changes = $log->properties->get('attributes', []);
                                    $old = $log->properties->get('old', []);
                                @endphp
                                @if(! empty($changes) && ! empty($old))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($changes as $field => $newVal)
                                            @if(isset($old[$field]) && $old[$field] !== $newVal)
                                                <span class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded">
                                                    <span class="font-medium">{{ $field }}</span>:
                                                    <span class="text-red-500 line-through">{{ is_array($old[$field]) ? json_encode($old[$field]) : $old[$field] }}</span>
                                                    →
                                                    <span class="text-green-600">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif(! empty($changes))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach(array_slice($changes, 0, 5) as $field => $val)
                                            <span class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded">
                                                <span class="font-medium">{{ $field }}</span>: {{ is_array($val) ? json_encode($val) : Str::limit((string) $val, 30) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $this->logs->links() }}</div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Activity Logs') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Activity will be recorded as users interact with the system.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
