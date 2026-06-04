<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

new
#[Title('Messages')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;

    public string $folder = 'inbox';

    #[Computed]
    public function messages()
    {
        $query = Message::whereNull('parent_message_id')
            ->with(['sender', 'receiver']);

        if ($this->folder === 'inbox') {
            $query->where('receiver_id', Auth::id());
        } elseif ($this->folder === 'sent') {
            $query->where('sender_id', Auth::id());
        } elseif ($this->folder === 'unread') {
            $query->where('receiver_id', Auth::id())->where('is_read', false);
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'inbox' => Message::where('receiver_id', Auth::id())->whereNull('parent_message_id')->count(),
            'unread' => Message::where('receiver_id', Auth::id())->where('is_read', false)->whereNull('parent_message_id')->count(),
            'sent' => Message::where('sender_id', Auth::id())->whereNull('parent_message_id')->count(),
        ];
    }

    public function selectFolder(string $folder): void
    {
        $this->folder = $folder;
        $this->resetPage();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Messages') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Communicate with students, parents, and staff.') }}</p>
        </div>
        <flux:button class="button" href="{{ route('teacher.messages.create') }}" wire:navigate icon="plus">
            {{ __('Compose') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Inbox') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['inbox']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Unread') }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($this->stats['unread']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Sent') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['sent']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 flex gap-2">
            <flux:button :variant="$folder === 'inbox' ? 'primary' : 'subtle'" wire:click="selectFolder('inbox')" :class="$folder === 'inbox' ? 'button' : ''" icon="inbox">{{ __('Inbox') }}</flux:button>
            <flux:button :variant="$folder === 'unread' ? 'primary' : 'subtle'" wire:click="selectFolder('unread')" :class="$folder === 'unread' ? 'button' : ''" icon="envelope">{{ __('Unread') }}</flux:button>
            <flux:button :variant="$folder === 'sent' ? 'primary' : 'subtle'" wire:click="selectFolder('sent')" :class="$folder === 'sent' ? 'button' : ''" icon="paper-airplane">{{ __('Sent') }}</flux:button>
        </div>

        @if($this->messages->count())
            <div class="space-y-2">
                @foreach($this->messages as $message)
                    <a href="{{ route('teacher.messages.show', $message->id) }}" wire:navigate
                        class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 {{ ! $message->is_read && $folder === 'inbox' ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            @if($folder === 'sent')
                                <flux:avatar :name="($message->receiver?->first_name ?? '') . ' ' . ($message->receiver?->last_name ?? '')" size="sm" />
                            @else
                                <flux:avatar :name="($message->sender?->first_name ?? '') . ' ' . ($message->sender?->last_name ?? '')" size="sm" />
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    @if($folder === 'sent')
                                        <span class="text-xs text-gray-500">{{ __('To') }}:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $message->receiver?->first_name }} {{ $message->receiver?->last_name }}</span>
                                    @else
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $message->sender?->first_name }} {{ $message->sender?->last_name }}</span>
                                    @endif
                                    @if(! $message->is_read && $folder === 'inbox')
                                        <flux:badge color="blue" size="sm">{{ __('New') }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $message->subject }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Str::limit($message->body, 80) }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 ml-3">{{ $message->created_at?->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-4">{{ $this->messages->links() }}</div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="envelope" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Messages') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
