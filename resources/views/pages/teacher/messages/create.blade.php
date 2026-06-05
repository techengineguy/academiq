<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('Compose Message')]
#[Layout('layouts.teacher')]
class extends Component {

    public array $receiver_ids = [];
    public string $subject = '';
    public string $body = '';

    #[Computed]
    public function recipients()
    {
        return User::where('id', '!=', Auth::id())
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'teacher', 'staff', 'student', 'parent'])
            ->orderBy('role')
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function selectedRecipients()
    {
        if (empty($this->receiver_ids)) {
            return collect();
        }
        return User::whereIn('id', $this->receiver_ids)->get();
    }

    public function removeRecipient(int $id): void
    {
        $this->receiver_ids = array_values(array_filter($this->receiver_ids, fn ($r) => (int) $r !== $id));
    }

    public function save(): void
    {
        $validated = $this->validate([
            'receiver_ids' => ['required', 'array', 'min:1'],
            'receiver_ids.*' => ['exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        foreach ($validated['receiver_ids'] as $receiverId) {
            Message::create([
                'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                'uuid' => Str::uuid(),
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'is_read' => false,
            ]);
        }

        Flux::toast(variant: 'success', text: __('Message sent successfully.'));
        $this->redirect(route('teacher.messages'), navigate: true);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Compose Message') }}</h1>
        </div>
        <flux:button variant="subtle" href="{{ route('teacher.messages') }}" wire:navigate icon="arrow-left">{{ __('Back') }}</flux:button>
    </div>
    <flux:card>
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:select label="{{ __('To') }}" variant="listbox" wire:model.live="receiver_ids" multiple searchable required>
                    @foreach($this->recipients as $user)
                        <flux:select.option value="{{ $user->id }}">
                            {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @if($this->selectedRecipients->count())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($this->selectedRecipients as $recipient)
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                {{ $recipient->first_name }} {{ $recipient->last_name }}
                                <button type="button" wire:click="removeRecipient({{ $recipient->id }})" class="ml-1 hover:text-indigo-900">&times;</button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <flux:input label="{{ __('Subject') }}" wire:model="subject" required />
            <flux:textarea label="{{ __('Message') }}" wire:model="body" rows="8" required />
            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button" icon="paper-airplane">{{ __('Send') }}</flux:button>
                <flux:button variant="subtle" href="{{ route('teacher.messages') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
</div>
