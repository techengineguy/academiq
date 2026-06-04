<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Leave Type')]
class extends Component {

    public string $name = '';
    public string $max_days = '';
    public string $applicable_to = 'all';
    public bool $requires_approval = true;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'max_days' => ['nullable', 'integer', 'min:1'],
            'applicable_to' => ['required', 'in:all,teacher,staff'],
            'requires_approval' => ['boolean'],
        ]);

        LeaveType::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'name' => $validated['name'],
            'max_days' => $validated['max_days'] ?: null,
            'applicable_to' => $validated['applicable_to'],
            'requires_approval' => $validated['requires_approval'],
        ]);

        Flux::toast(variant: 'success', text: __('Leave type created successfully.'));

        $this->redirect(route('leave-types.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Name') }}" wire:model="name" placeholder="{{ __('e.g., Annual Leave, Sick Leave') }}" required />

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Max Days Per Year') }}" type="number" wire:model="max_days" min="1" placeholder="{{ __('Leave blank for unlimited') }}" />

            <flux:select label="{{ __('Applicable To') }}" variant="listbox" wire:model="applicable_to" required>
                <flux:select.option value="all">{{ __('All Staff') }}</flux:select.option>
                <flux:select.option value="teacher">{{ __('Teachers Only') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff Only') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:checkbox label="{{ __('Requires Approval') }}" wire:model="requires_approval" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-leave-type')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
