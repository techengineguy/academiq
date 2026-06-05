<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Concerns\ScopesToParentChildren;
use App\Models\ClassSubject;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('Compose Message')]
#[Layout('layouts.parent')]
class extends Component {
    use ScopesToParentChildren;

    public string $receiver_id = '';
    public string $subject = '';
    public string $body = '';

    #[Computed]
    public function recipients()
    {
        // Show teachers assigned to parent's children's classes + admins
        $classIds = $this->parentChildren()->pluck('class_id')->unique();

        $teacherIds = ClassSubject::whereIn('class_id', $classIds)
            ->pluck('teacher_id')
            ->unique();

        return User::where('is_active', true)
            ->where(function ($q) use ($teacherIds) {
                $q->whereIn('id', $teacherIds)->orWhere('role', 'admin');
            })
            ->orderBy('role')
            ->orderBy('first_name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        Message::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        Flux::toast(variant: 'success', text: __('Message sent successfully.'));

        $this->redirect(route('parent.messages'), navigate: true);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Compose Message') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Message a teacher or school admin.') }}</p>
        </div>
        <flux:button variant="subtle" href="{{ route('parent.messages') }}" wire:navigate icon="arrow-left">{{ __('Back') }}</flux:button>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-6">
            <flux:select label="{{ __('To') }}" variant="listbox" wire:model="receiver_id" searchable required>
                <flux:select.option value="">{{ __('Select Recipient') }}</flux:select.option>
                @foreach($this->recipients as $user)
                    <flux:select.option value="{{ $user->id }}">
                        {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input label="{{ __('Subject') }}" wire:model="subject" required />
            <flux:textarea label="{{ __('Message') }}" wire:model="body" rows="8" required />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button" icon="paper-airplane">{{ __('Send') }}</flux:button>
                <flux:button variant="subtle" href="{{ route('parent.messages') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
</div>
