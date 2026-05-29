<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Leave Type')]
class extends Component {

    public ?LeaveType $leaveType = null;

    public string $name = '';
    public string $max_days = '';
    public string $applicable_to = 'all';
    public bool $requires_approval = true;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadLeaveType($id);
        }
    }

    #[On('edit-leave-type')]
    public function loadLeaveType(int $id): void
    {
        $this->leaveType = LeaveType::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $this->name = $this->leaveType->name;
        $this->max_days = (string) ($this->leaveType->max_days ?? '');
        $this->applicable_to = $this->leaveType->applicable_to ?? 'all';
        $this->requires_approval = $this->leaveType->requires_approval;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'max_days' => ['nullable', 'integer', 'min:1'],
            'applicable_to' => ['required', 'in:all,teacher,staff'],
            'requires_approval' => ['boolean'],
        ]);

        $this->leaveType->update([
            'name' => $validated['name'],
            'max_days' => $validated['max_days'] ?: null,
            'applicable_to' => $validated['applicable_to'],
            'requires_approval' => $validated['requires_approval'],
        ]);

        Flux::toast(variant: 'success', text: __('Leave type updated successfully.'));

        $this->redirect(route('leave-types.index'), navigate: true);
    }
};
?>
<div>
    @if($this->leaveType)
        <form wire:submit="update" class="space-y-6">
            <flux:input label="{{ __('Name') }}" wire:model="name" required />

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
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-leave-type')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
