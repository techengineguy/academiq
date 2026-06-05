<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Mail\AccountantInvitationMail;
use App\Models\AccountantInvitation;
use App\Models\Accountant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Accountants')]
class extends Component {
    use WithPagination;

    public ?int $invitationIdToCancel = null;

    // Invite form
    public string $invite_first_name = '';
    public string $invite_last_name = '';
    public string $invite_email = '';

    #[Computed]
    public function accountants()
    {
        return Accountant::with('user')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'accountantsPage');
    }

    #[Computed]
    public function invitations()
    {
        return AccountantInvitation::where('institution_id', Auth::user()->institution_id)
            ->with('invitedBy')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'invitationsPage');
    }

    public function confirmCancel(int $id): void
    {
        $this->invitationIdToCancel = $id;
        Flux::modal('cancel-invitation')->show();
    }

    public function cancelInvitation(): void
    {
        $invitation = AccountantInvitation::where('institution_id', Auth::user()->institution_id)
            ->where('id', $this->invitationIdToCancel)
            ->firstOrFail();

        $invitation->update(['status' => 'expired']);
        $this->invitationIdToCancel = null;

        Flux::modal('cancel-invitation')->close();
        Flux::toast(text: 'Invitation cancelled.', variant: 'success');
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'invite_first_name' => 'required|string|max:255',
            'invite_last_name' => 'required|string|max:255',
            'invite_email' => 'required|email|max:255',
        ], [], [
            'invite_first_name' => 'first name',
            'invite_last_name' => 'last name',
            'invite_email' => 'email address',
        ]);

        $user = Auth::user();

        $existing = AccountantInvitation::where('institution_id', $user->institution_id)
            ->where('email', $this->invite_email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existing) {
            $this->addError('invite_email', 'A pending invitation has already been sent to this email address.');

            return;
        }

        $invitation = AccountantInvitation::create([
            'uuid' => Str::uuid(),
            'institution_id' => $user->institution_id,
            'invited_by' => $user->id,
            'email' => $this->invite_email,
            'first_name' => $this->invite_first_name,
            'last_name' => $this->invite_last_name,
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load('institution');

        Mail::to($invitation->email)->send(new AccountantInvitationMail($invitation));

        $this->reset('invite_first_name', 'invite_last_name', 'invite_email');
        $this->resetPage('invitationsPage');

        Flux::toast(text: "Invitation sent to {$invitation->email}.", variant: 'success');
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Accountants') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage accountants and send invitations.') }}</p>
        </div>
        @if(\Spatie\Multitenancy\Models\Tenant::current()?->hasFeature('accountant_management'))
        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('invite-accountant')" icon="envelope">
            {{ __('Invite Accountant') }}
        </flux:button>
        @endif    </div>

    {{-- Active Accountants --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Active Accountants') }}</flux:heading>
        @if($this->accountants->count())
            <flux:table :paginate="$this->accountants">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Employee ID') }}</flux:table.column>
                    <flux:table.column>{{ __('Designation') }}</flux:table.column>
                    <flux:table.column>{{ __('Joined') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->accountants as $accountant)
                        <flux:table.row wire:key="accountant-{{ $accountant->id }}">
                            <flux:table.cell>{{ $accountant->first_name }} {{ $accountant->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $accountant->email }}</flux:table.cell>
                            <flux:table.cell>{{ $accountant->employee_id ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $accountant->designation ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $accountant->joining_date?->format('M d, Y') ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$accountant->status === 'active' ? 'green' : 'zinc'" size="sm">
                                    {{ ucfirst($accountant->status) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No accountants yet. Invite one to get started.') }}</flux:text>
        @endif
    </flux:card>

    {{-- Pending Invitations --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Invitations') }}</flux:heading>
        @if($this->invitations->count())
            <flux:table :paginate="$this->invitations">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Invited By') }}</flux:table.column>
                    <flux:table.column>{{ __('Sent') }}</flux:table.column>
                    <flux:table.column>{{ __('Expires') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->invitations as $invitation)
                        <flux:table.row wire:key="invitation-{{ $invitation->id }}">
                            <flux:table.cell>{{ $invitation->first_name }} {{ $invitation->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $invitation->email }}</flux:table.cell>
                            <flux:table.cell>{{ $invitation->invitedBy?->first_name }} {{ $invitation->invitedBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $invitation->created_at->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $invitation->expires_at->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge
                                    :color="match($invitation->status) {
                                        'accepted' => 'green',
                                        'expired' => 'red',
                                        default => $invitation->isExpired() ? 'red' : 'yellow'
                                    }"
                                    size="sm"
                                >
                                    {{ $invitation->status === 'pending' && $invitation->isExpired() ? 'Expired' : ucfirst($invitation->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($invitation->isPending())
                                    <flux:button wire:click="confirmCancel({{ $invitation->id }})" size="sm" variant="danger">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text>{{ __('No invitations sent yet.') }}</flux:text>
        @endif
    </flux:card>
</div>

<flux:modal name="cancel-invitation" class="min-w-[20rem]">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('Cancel Invitation') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Are you sure you want to cancel this invitation? The recipient will no longer be able to accept it.') }}</flux:text>
        </div>
        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Keep') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="cancelInvitation" variant="danger">{{ __('Cancel Invitation') }}</flux:button>
        </div>
    </div>
</flux:modal>

    <x-slide id="invite-accountant" title="{{ __('Invite Accountant') }}" size="lg">
        <form wire:submit="sendInvitation" class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('First Name') }}</flux:label>
                    <flux:input wire:model="invite_first_name" :placeholder="__('First name')" required />
                    <flux:error name="invite_first_name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Last Name') }}</flux:label>
                    <flux:input wire:model="invite_last_name" :placeholder="__('Last name')" required />
                    <flux:error name="invite_last_name" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Email Address') }}</flux:label>
                <flux:input wire:model="invite_email" type="email" :placeholder="__('accountant@example.com')" required />
                <flux:error name="invite_email" />
                <flux:description>{{ __('An invitation link will be emailed. Expires in 7 days.') }}</flux:description>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" icon="paper-airplane" class="button">
                    {{ __('Send Invitation') }}
                </flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('invite-accountant')">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </x-slide>
</div>
