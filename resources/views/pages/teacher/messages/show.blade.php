<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('Message')]
#[Layout('layouts.teacher')]
class extends Component {

    public int $id;
    public string $replyBody = '';

    public function mount(int $id): void
    {
        $this->id = $id;
        $message = Message::findOrFail($id);
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403);
        }
        if ($message->receiver_id === Auth::id() && ! $message->is_read) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    #[Computed]
    public function message()
    {
        return Message::with(['sender', 'receiver', 'replies.sender', 'replies.receiver'])
            ->findOrFail($this->id);
    }

    public function reply(): void
    {
        $this->validate(['replyBody' => ['required', 'string']]);
        $original = $this->message;
        $receiverId = $original->sender_id === Auth::id() ? $original->receiver_id : $original->sender_id;
        Message::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'parent_message_id' => $original->id,
            'subject' => 'Re: ' . $original->subject,
            'body' => $this->replyBody,
            'is_read' => false,
        ]);
        $this->replyBody = '';
        Flux::toast(variant: 'success', text: __('Reply sent.'));
        unset($this->message);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->message->subject }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('From') }}: {{ $this->message->sender?->first_name }} {{ $this->message->sender?->last_name }}
                &middot; {{ $this->message->created_at?->format('M d, Y H:i') }}
            </p>
        </div>
        <flux:button variant="subtle" href="{{ route('teacher.messages') }}" wire:navigate icon="arrow-left">{{ __('Back') }}</flux:button>
    </div>
    <flux:card>
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-zinc-700">
            <flux:avatar :name="($this->message->sender?->first_name ?? '') . ' ' . ($this->message->sender?->last_name ?? '')" />
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->message->sender?->first_name }} {{ $this->message->sender?->last_name }}</p>
                <p class="text-xs text-gray-500">{{ __('To') }}: {{ $this->message->receiver?->first_name }} {{ $this->message->receiver?->last_name }}</p>
            </div>
        </div>
        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $this->message->body }}</div>
    </flux:card>
    @if($this->message->replies->count())
        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Replies') }}</h2>
            @foreach($this->message->replies as $reply)
                <flux:card>
                    <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200 dark:border-zinc-700">
                        <flux:avatar :name="($reply->sender?->first_name ?? '') . ' ' . ($reply->sender?->last_name ?? '')" size="sm" />
                        <div>
                            <p class="text-sm font-medium">{{ $reply->sender?->first_name }} {{ $reply->sender?->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ $reply->created_at?->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $reply->body }}</div>
                </flux:card>
            @endforeach
        </div>
    @endif
    <flux:card>
        <form wire:submit="reply" class="space-y-4">
            <flux:textarea label="{{ __('Reply') }}" wire:model="replyBody" rows="5" required />
            <flux:button type="submit" variant="primary" class="button" icon="paper-airplane">{{ __('Send Reply') }}</flux:button>
        </form>
    </flux:card>
</div>
</div>
