<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Edit Role')]
class extends Component {

    public ?Role $role = null;

    public string $name = '';
    public string $description = '';
    public array $selectedPermissions = [];
    public array $selectedUsers = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadRole($id);
        }
    }

    #[On('edit-role')]
    public function loadRole(int $id): void
    {
        $this->role = Role::with(['permissions', 'users'])
            ->findOrFail($id);

        $this->name = $this->role->name;
        $this->description = (string) ($this->role->description ?? '');
        $this->selectedPermissions = $this->role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->selectedUsers = $this->role->users->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    #[Computed]
    public function permissionsByModule()
    {
        return Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    #[Computed]
    public function users()
    {
        return User::where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function toggleModule(string $module): void
    {
        $modulePermissions = Permission::where('module', $module)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $allSelected = empty(array_diff($modulePermissions, $this->selectedPermissions));

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $modulePermissions));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $modulePermissions)));
        }
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'selectedPermissions' => ['array'],
            'selectedUsers' => ['array'],
        ]);

        $this->role->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
        ]);

        // Sync permissions
        $permPivot = collect($this->selectedPermissions)->mapWithKeys(fn (string $permId) => [
            $permId => [
                'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                'uuid' => Str::uuid(),
            ],
        ])->all();
        $this->role->permissions()->sync($permPivot);

        // Sync users
        $userPivot = collect($this->selectedUsers)->mapWithKeys(fn (string $userId) => [
            $userId => [
                'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                'uuid' => Str::uuid(),
            ],
        ])->all();
        $this->role->users()->sync($userPivot);

        Flux::toast(variant: 'success', text: __('Role updated successfully.'));

        $this->redirect(route('roles.index'), navigate: true);
    }
};
?>
<div>
    @if($this->role)
        <form wire:submit="update" class="space-y-6">
            <flux:input label="{{ __('Role Name') }}" wire:model="name" required />

            <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="2" />

            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Permissions') }}</p>

                @forelse($this->permissionsByModule as $module => $permissions)
                    <div class="mb-4 rounded-lg border border-gray-200 dark:border-zinc-700 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $module) }}</span>
                            <flux:button type="button" size="sm" variant="subtle" wire:click="toggleModule('{{ $module }}')">
                                {{ __('Toggle All') }}
                            </flux:button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($permissions as $permission)
                                <flux:checkbox
                                    label="{{ $permission->name }}"
                                    value="{{ $permission->id }}"
                                    wire:model="selectedPermissions"
                                />
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">{{ __('No permissions defined yet.') }}</p>
                @endforelse
            </div>

            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Assign Users') }}</p>
                <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700 p-4 space-y-2">
                    @foreach($this->users as $user)
                        <flux:checkbox
                            label="{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})"
                            value="{{ $user->id }}"
                            wire:model="selectedUsers"
                        />
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-role')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
