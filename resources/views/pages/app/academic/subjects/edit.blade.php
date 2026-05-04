<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public ?Subject $subject = null;

    public string $name = '';
    public string $code = '';
    public string $type = '';
    public string $description = '';
    public string $status = 'active';

    #[On('edit-subject')]
    public function loadSubject(string $uuid): void
    {
        $this->subject = Subject::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)->firstOrFail();

        $this->name = $this->subject->name;
        $this->code = $this->subject->code;
        $this->type = $this->subject->type;
        $this->description = $this->subject->description;
        $this->status = $this->subject->status;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:theory,practical,both'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->subject->update([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Subject updated successfully.'));

        $this->redirect(route('subjects.index'), navigate: true);
    }
};
?>

<div>
    @if($this->subject)
        <form wire:submit="update" class="space-y-6">
            <flux:input
                label="{{ __('Name') }}"
                wire:model="name"
                required
            />
            <flux:input
                label="{{ __('Code') }}"
                wire:model="code"
                required
            />
            <flux:select
                label="{{ __('Type') }}"
                variant="listbox"
                wire:model="type"
                required
            >
                <flux:select.option value="theory">{{ __('Theory') }}</flux:select.option>
                <flux:select.option value="practical">{{ __('Practical') }}</flux:select.option>
                <flux:select.option value="both">{{ __('Both') }}</flux:select.option>
            </flux:select>
            <flux:textarea
                label="{{ __('Description') }}"
                wire:model="description"
            />
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-subject')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>


