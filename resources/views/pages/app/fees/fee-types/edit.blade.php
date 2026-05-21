<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\FeeType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Flux\Flux;

new #[Title('Edit Fee Type')]
class extends Component {

    public ?FeeType $feeType = null;

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public bool $is_refundable = false;
    public string $status = 'active';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadFeeType($id);
        }
    }

    #[On('edit-fee-type')]
    public function loadFeeType(int $id): void
    {
        $this->feeType = FeeType::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $this->name = $this->feeType->name;
        $this->code = $this->feeType->code;
        $this->description = (string) ($this->feeType->description ?? '');
        $this->is_refundable = $this->feeType->is_refundable;
        $this->status = $this->feeType->status;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('fee_types', 'code')->ignore($this->feeType?->id)],
            'description' => ['nullable', 'string'],
            'is_refundable' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->feeType->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'is_refundable' => $validated['is_refundable'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Fee type updated successfully.'));

        $this->redirect(route('fee-types.index'), navigate: true);
    }
};
?>
<div>
    @if($this->feeType)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Name') }}" wire:model="name" required />
                <flux:input label="{{ __('Code') }}" wire:model="code" required />
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
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-fee-type')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
