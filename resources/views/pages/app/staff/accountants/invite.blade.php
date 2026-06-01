<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Mail\AccountantInvitationMail;
use App\Models\AccountantInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Invite Accountant')]
class extends Component {

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';

    public function send(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = Auth::user();

        // Check for an existing pending invitation for this email in this institution
        $existing = AccountantInvitation::where('institution_id', $user->institution_id)
            ->where('email', $this->email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existing) {
            $this->addError('email', 'A pending invitation has already been sent to this email address.');

            return;
        }

        $invitation = AccountantInvitation::create([
            'uuid' => Str::uuid(),
            'institution_id' => $user->institution_id,
            'invited_by' => $user->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load('institution');

        Mail::to($invitation->email)->send(new AccountantInvitationMail($invitation));

        Flux::toast(text: "Invitation sent to {$invitation->email}.", variant: 'success');
        $this->redirect(route('accountants.index'), navigate: true);
    }
};
?>
<div>
<div class="max-w-2xl space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Invite Accountant') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Send an email invitation to a new accountant.') }}</p>
    </div>

    <flux:card>
        <form wire:submit="send" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('First Name') }}</flux:label>
                    <flux:input wire:model="first_name" :placeholder="__('First name')" required />
                    <flux:error name="first_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Last Name') }}</flux:label>
                    <flux:input wire:model="last_name" :placeholder="__('Last name')" required />
                    <flux:error name="last_name" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Email Address') }}</flux:label>
                <flux:input wire:model="email" type="email" :placeholder="__('accountant@example.com')" required />
                <flux:error name="email" />
                <flux:description>{{ __('An invitation email will be sent to this address. The link expires in 7 days.') }}</flux:description>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" icon="paper-airplane">
                    {{ __('Send Invitation') }}
                </flux:button>
                <flux:button :href="route('accountants.index')" wire:navigate variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
</div>
