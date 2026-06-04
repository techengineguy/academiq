<?php

use Livewire\Component;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $name = '';
    public $code = '';
    public $type = '';
    public $description = '';
    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects',
            'type' => 'required|in:theory,practical,both',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $institution = Tenant::current();

        Subject::create([
            'tenant_id' => Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Subject created successfully.'));

        $this->redirect(route('subjects.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Name') }}" placeholder="{{ __('Enter subject name') }}" wire:model="name" required />
        <flux:input label="{{ __('Code') }}" placeholder="{{ __('Enter subject code') }}" wire:model="code" required />
        <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
            <flux:select.option value="">{{ __('Select Type') }}</flux:select.option>
            <flux:select.option value="theory">{{ __('Theory') }}</flux:select.option>
            <flux:select.option value="practical">{{ __('Practical') }}</flux:select.option>
            <flux:select.option value="both">{{ __('Both') }}</flux:select.option>
        </flux:select>
        <flux:textarea label="{{ __('Description') }}" placeholder="{{ __('Enter subject description') }}" wire:model="description" />
        <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
        </flux:select>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-subject')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>


