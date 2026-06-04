<?php

use App\Models\Scholarship;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class extends Component
{
    use Interactions;

    public $name = '';

    public $description = '';

    public $type = 'fixed_amount';

    public $value = '';

    public $eligibility_criteria = '';

    public $valid_from = '';

    public $valid_to = '';

    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'eligibility_criteria' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'required|in:active,inactive',
        ]);

        $institution = Tenant::current();

        // Convert eligibility_criteria to JSON if provided
        $criteria = null;
        if ($this->eligibility_criteria) {
            $criteria = json_encode(['description' => $this->eligibility_criteria]);
        }

        Scholarship::create([
            'tenant_id' => Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'eligibility_criteria' => $criteria,
            'valid_from' => $this->valid_from ?: null,
            'valid_to' => $this->valid_to ?: null,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Scholarship created successfully.'));

        $this->redirect(route('scholarships.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 gap-4">
            <flux:input label="{{ __('Scholarship Name') }}" placeholder="{{ __('e.g., Merit Scholarship') }}" wire:model="name" required />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <flux:textarea label="{{ __('Description') }}" placeholder="{{ __('Scholarship description and details') }}" wire:model="description" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
                <flux:select.option value="fixed_amount">{{ __('Fixed Amount') }}</flux:select.option>
                <flux:select.option value="percentage">{{ __('Percentage') }}</flux:select.option>
            </flux:select>

            <flux:input label="{{ __('Value') }}" type="number" step="0.01" placeholder="{{ __('Enter value') }}" wire:model="value" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Valid From') }}" wire:model="valid_from" />
            <flux:date-picker label="{{ __('Valid To') }}" wire:model="valid_to" />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <flux:textarea label="{{ __('Eligibility Criteria') }}" placeholder="{{ __('e.g., GPA 3.5+, Financial need requirement') }}" wire:model="eligibility_criteria" />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" class="button" variant="primary">
                {{ __('Create Scholarship') }}
            </flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$tsui.close.slide('create-scholarship')">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>

