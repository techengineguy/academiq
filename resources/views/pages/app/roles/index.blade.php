<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Roles & Permissions')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $roleIdToDelete = null;

    #[Computed]
    public function roles()
    {
        return Role::withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function totalRoles(): int
    {
        return (int) Role::count();
    }

    #[Computed]
    public function totalUsersWithRoles(): int
    {
        return (int) User::whereHas('roles')
            ->count();
    }

    public function confirmDelete(int $id): void
    {
        $this->roleIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this role? Users assigned to it will lose these permissions.'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->roleIdToDelete) {
            return;
        }

        $role = Role::findOrFail($this->roleIdToDelete);
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        $this->roleIdToDelete = null;
        unset($this->roles);

        Flux::toast(variant: 'success', text: __('Role deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Roles & Permissions') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage roles and assign permissions to control access.') }}</p>
        </div>

        <div class="flex gap-2">
            <flux:button variant="subtle" x-on:click="$tsui.open.slide('assign-user-roles')" icon="user-group">
                {{ __('Assign Users') }}
            </flux:button>
            <flux:button class="button" x-on:click="$tsui.open.slide('create-role')" icon="plus">
                {{ __('New Role') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Roles') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalRoles) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Users with Roles') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalUsersWithRoles) }}</p>
        </flux:card>
    </div>

    <flux:card>
        @if($this->roles->count())
            <flux:table :paginate="$this->roles">
                <flux:table.columns>
                    <flux:table.column>{{ __('Role') }}</flux:table.column>
                    <flux:table.column>{{ __('Slug') }}</flux:table.column>
                    <flux:table.column>{{ __('Permissions') }}</flux:table.column>
                    <flux:table.column>{{ __('Users') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->roles as $role)
                    <flux:table.rows>
                        <flux:table.row :key="$role->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $role->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $role->slug }}</code>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ $role->permissions_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green">{{ $role->users_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ Str::limit($role->description, 30) ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-role'), $wire.dispatch('edit-role', { id: {{ $role->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $role->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Roles') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create roles to manage user access and permissions.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-role" title="{{ __('Create Role') }}" size="xl">
        <livewire:pages::app.roles.create />
    </x-slide>

    <x-slide id="edit-role" title="{{ __('Edit Role') }}" size="xl">
        <livewire:pages::app.roles.edit :id="$slideData['id'] ?? null" />
    </x-slide>

    <x-slide id="assign-user-roles" title="{{ __('Assign User Roles') }}" size="xl">
        <livewire:pages::app.roles.assign />
    </x-slide>
</div>
