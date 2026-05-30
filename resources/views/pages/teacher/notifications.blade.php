<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new
#[Title('Notifications')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;

    public string $filterStatus = 'all';

    #[Computed]
    public function notifications()
    {
        $query = Notification::where('tenant_id', Auth::user()->tenant_id)
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at');

        if ($this->filterStatus === 'unread') {
            $query->where('is_read', false);
        } elseif ($this->filterStatus === 'read') {
            $query->where('is_read', true);
        }

        return $query->paginate(20);
    }

    #[Computed]
    public function stats(): array
    {
        $base = Notification::where('tenant_id', Auth::user()->tenant_id)->where('user_id', Auth::id());

        return [
            'total' => (clone $base)->count(),
            'unread' => (clone $base)->where('is_read', false)->count(),
        ];
    }

    public function markAsRead(int $id): void
    {
        Notification::where('user_id', Auth::id())->where('id', $id)
            ->update(['is_read' => true, 'read_at' => now()]);
        unset($this->notifications, $this->stats);
    }

    public function markAllAsRead(): void
    {
        Notification::where('user_id', Auth::id())->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        Flux::toast(variant: 'success', text: __('All notifications marked as read.'));
        unset($this->notifications, $this->stats);
    }

    public function selectFilter(string $filter): void
    {
        $this->filterStatus = $filter;
        $this->resetPage();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Notifications') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Stay up to date with school activity.') }}</p>
        </div>
        @if($this->stats['unread'] > 0)
            <flux:button variant="subtle" wire:click="markAllAsRead" icon="check">{{ __('Mark All Read') }}</flux:button>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['total']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Unread') }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($this->stats['unread']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 flex gap-2">
            <flux:button :variant="$filterStatus === 'all' ? 'primary' : 'subtle'" wire:click="selectFilter('all')" :class="$filterStatus === 'all' ? 'button' : ''">{{ __('All') }}</flux:button>
            <flux:button :variant="$filterStatus === 'unread' ? 'primary' : 'subtle'" wire:click="selectFilter('unread')" :class="$filterStatus === 'unread' ? 'button' : ''">{{ __('Unread') }}</flux:button>
            <flux:button :variant="$filterStatus === 'read' ? 'primary' : 'subtle'" wire:click="selectFilter('read')" :class="$filterStatus === 'read' ? 'button' : ''">{{ __('Read') }}</flux:button>
        </div>

        @if($this->notifications->count())
            <div class="space-y-2">
                @foreach($this->notifications as $notification)
                    <div class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 dark:border-zinc-700 {{ ! $notification->is_read ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                        @php
                            $iconColor = match($notification->type ?? 'info') {
                                'success' => 'text-green-600 bg-green-100 dark:bg-green-900/30',
                                'warning' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30',
                                'error' => 'text-red-600 bg-red-100 dark:bg-red-900/30',
                                default => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30',
                            };
                            $iconName = match($notification->type ?? 'info') {
                                'success' => 'check-circle', 'warning' => 'exclamation-triangle', 'error' => 'x-circle', default => 'information-circle',
                            };
                        @endphp
                        <div class="p-2 rounded-lg {{ $iconColor }} shrink-0">
                            <flux:icon :name="$iconName" class="size-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $notification->title }}</p>
                                @if(! $notification->is_read)
                                    <flux:badge color="blue" size="sm">{{ __('New') }}</flux:badge>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $notification->message }}</p>
                            <span class="text-xs text-gray-400">{{ $notification->created_at?->diffForHumans() }}</span>
                        </div>
                        @if(! $notification->is_read)
                            <flux:button size="sm" variant="subtle" icon="check" wire:click="markAsRead({{ $notification->id }})" />
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $this->notifications->links() }}</div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="bell" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Notifications') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
