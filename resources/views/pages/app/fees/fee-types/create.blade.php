<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\FeeType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Fee Type')]
class extends Component {

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public bool $is_refundable = false;
    public string $status = 'active';

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:fee_types,code'],
            'description' => ['nullable', 'string'],
            'is_refundable' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        FeeType::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'is_refundable' => $validated['is_refundable'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Fee type created successfully.'));

        $this->redirect(route('fee-types.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Name') }}" wire:model="name" placeholder="{{ __('e.g., Tuition Fee') }}" required />
            <flux:input label="{{ __('Code') }}" wire:model="code" placeholder="{{ __('e.g., TUI-001') }}" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
            <div class="flex items-end">
                <flux:checkbox label="{{ __('Refundable') }}" wire:model="is_refundable" />
            </div>
        </div>

        <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-fee-type')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
