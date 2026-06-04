<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Assign User Roles')]
class extends Component {

    public ?User $selectedUser = null;
    public array $selectedRoles = [];

    #[Computed]
    public function users()
    {
        return User::where('is_active', true)
            ->with('roles')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')
            ->get();
    }

    #[On('assign-user-roles')]
    public function loadUser(int $id): void
    {
        $this->selectedUser = User::findOrFail($id);
        $this->selectedRoles = $this->selectedUser->roles->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function save(): void
    {
        if (! $this->selectedUser) {
            return;
        }

        $pivotData = collect($this->selectedRoles)->mapWithKeys(fn (string $roleId) => [
            $roleId => [
                'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                'uuid' => Str::uuid(),
            ],
        ])->all();

        $this->selectedUser->roles()->sync($pivotData);

        Flux::toast(variant: 'success', text: __('Roles updated for :name.', ['name' => $this->selectedUser->first_name]));

        $this->selectedUser = null;
        $this->selectedRoles = [];
        unset($this->users);

        $this->redirect(route('roles.index'), navigate: true);
    }
};
?>
<div>
    @if($this->selectedUser)
        <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-zinc-800">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $this->selectedUser->first_name }} {{ $this->selectedUser->last_name }}
            </p>
            <p class="mt-1 text-xs text-gray-500">{{ $this->selectedUser->email }} &middot; {{ ucfirst($this->selectedUser->role) }}</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Assign Roles') }}</p>
                <div class="space-y-2 rounded-lg border border-gray-200 dark:border-zinc-700 p-4">
                    @foreach($this->roles as $role)
                        <flux:checkbox
                            label="{{ $role->name }}"
                            description="{{ $role->description }}"
                            value="{{ $role->id }}"
                            wire:model="selectedRoles"
                        />
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('assign-user-roles')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="space-y-3">
            <p class="text-sm text-gray-500 mb-4">{{ __('Select a user to manage their roles.') }}</p>
            @foreach($this->users as $user)
                <div
                    class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 cursor-pointer transition-colors"
                    wire:click="loadUser({{ $user->id }})"
                >
                    <div class="flex items-center gap-3">
                        <flux:avatar :name="$user->first_name . ' ' . $user->last_name" size="sm" />
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex gap-1 flex-wrap">
                        @forelse($user->roles as $role)
                            <flux:badge size="sm" color="blue">{{ $role->name }}</flux:badge>
                        @empty
                            <flux:badge size="sm" color="gray">{{ __('No role') }}</flux:badge>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
