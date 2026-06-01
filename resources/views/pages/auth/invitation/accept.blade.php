<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\AccountantInvitation;
use App\Models\Accountant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

new
#[Title('Accept Invitation')]
#[Layout('layouts.guest')]
class extends Component {

    public ?AccountantInvitation $invitation = null;
    public bool $isValid = false;
    public bool $isAccepted = false;

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->invitation = AccountantInvitation::with('institution')
            ->where('token', $token)
            ->first();

        if (! $this->invitation) {
            return;
        }

        if ($this->invitation->status === 'accepted') {
            $this->isAccepted = true;

            return;
        }

        if ($this->invitation->isExpired()) {
            $this->invitation->update(['status' => 'expired']);

            return;
        }

        $this->isValid = true;
    }

    public function accept(): void
    {
        if (! $this->invitation || ! $this->isValid) {
            return;
        }

        $this->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        // Create user account
        $user = User::create([
            'uuid' => Str::uuid(),
            'tenant_id' => $this->invitation->institution->uuid,
            'institution_id' => $this->invitation->institution_id,
            'first_name' => $this->invitation->first_name,
            'last_name' => $this->invitation->last_name,
            'username' => Str::slug($this->invitation->first_name . '.' . $this->invitation->last_name . '.' . Str::random(4)),
            'email' => $this->invitation->email,
            'password' => Hash::make($this->password),
            'role' => 'accountant',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create accountant record
        Accountant::create([
            'uuid' => Str::uuid(),
            'tenant_id' => $this->invitation->institution->uuid,
            'institution_id' => $this->invitation->institution_id,
            'user_id' => $user->id,
            'first_name' => $this->invitation->first_name,
            'last_name' => $this->invitation->last_name,
            'email' => $this->invitation->email,
            'status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        // Mark invitation as accepted
        $this->invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Log the user in
        Auth::login($user);

        $this->redirect(route('accountant.dashboard'), navigate: false);
    }
};
?>
<div class="flex flex-col gap-6 max-w-md mx-auto">
    @if($this->isAccepted)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6 text-center">
            <flux:icon.check-circle class="w-12 h-12 text-green-500 mx-auto mb-3" />
            <flux:heading size="lg">{{ __('Invitation Already Accepted') }}</flux:heading>
            <flux:text class="mt-2">{{ __('This invitation has already been used. Please log in to access your account.') }}</flux:text>
            <flux:button :href="route('login')" class="mt-4">{{ __('Log In') }}</flux:button>
        </div>

    @elseif(! $this->invitation || $this->invitation->isExpired())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6 text-center">
            <flux:icon.x-circle class="w-12 h-12 text-red-500 mx-auto mb-3" />
            <flux:heading size="lg">{{ __('Invalid or Expired Invitation') }}</flux:heading>
            <flux:text class="mt-2">{{ __('This invitation link is no longer valid. Please contact your administrator for a new invitation.') }}</flux:text>
        </div>

    @else
        <x-auth-header
            :title="__('Welcome, :name!', ['name' => $this->invitation->first_name])"
            :description="__('You\'ve been invited to join :institution as an accountant. Create your password to get started.', ['institution' => $this->invitation->institution->name])"
        />

        <form wire:submit="accept" class="flex flex-col gap-5 bg-white dark:bg-zinc-900 rounded-lg shadow-xl p-8">
            <div>
                <flux:input
                    :label="__('Email')"
                    :value="$this->invitation->email"
                    type="email"
                    disabled
                />
            </div>

            <flux:field>
                <flux:label>{{ __('Password') }}</flux:label>
                <flux:input
                    wire:model="password"
                    type="password"
                    :placeholder="__('Create a strong password')"
                    viewable
                    required
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirm Password') }}</flux:label>
                <flux:input
                    wire:model="password_confirmation"
                    type="password"
                    :placeholder="__('Repeat your password')"
                    viewable
                    required
                />
            </flux:field>

            <flux:button type="submit" class="w-full">
                {{ __('Create Account & Accept Invitation') }}
            </flux:button>
        </form>
    @endif
</div>
