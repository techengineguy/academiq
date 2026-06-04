<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Role')]
class extends Component {

    public string $name = '';
    public string $description = '';
    public array $selectedPermissions = [];

    #[Computed]
    public function permissionsByModule()
    {
        return Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
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

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'selectedPermissions' => ['array'],
        ]);

        $slug = Str::slug($validated['name']);

        $role = Role::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
        ]);

        if (! empty($this->selectedPermissions)) {
            $pivotData = collect($this->selectedPermissions)->mapWithKeys(fn (string $permId) => [
                $permId => [
                    'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                    'uuid' => Str::uuid(),
                ],
            ])->all();
            $role->permissions()->attach($pivotData);
        }

        Flux::toast(variant: 'success', text: __('Role created successfully.'));

        $this->redirect(route('roles.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Role Name') }}" wire:model="name" placeholder="{{ __('e.g., Class Teacher, Accountant') }}" required />

        <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="2" placeholder="{{ __('What can this role do?') }}" />

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
                <p class="text-sm text-gray-500 py-4 text-center">{{ __('No permissions defined yet. Seed permissions first.') }}</p>
            @endforelse
        </div>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-role')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
