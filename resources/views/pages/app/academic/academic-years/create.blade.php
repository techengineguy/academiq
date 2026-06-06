<?php

use Livewire\Component;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Str;
use Flux\Flux;

new class extends Component {

    public $name = '';
    public $start_date = '';
    public $end_date = '';
    public $is_current = false;
    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $institution = Tenant::current();

        AcademicYear::create([
            'tenant_id' => Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $validated['is_current'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Academic year created successfully.'));

        $this->redirect(route('academic-years.index'), navigate: true);
    }
};
?>

<div>
    <div class="space-y-6">
        <form wire:submit="save" class="space-y-6">
            <!-- Name Field -->
            <flux:input 
                label="{{ __('Name') }}" 
                placeholder="e.g., 2024-2025"
                wire:model="name" 
            />

            <!-- Start Date Field -->
            <flux:date-picker label="{{ __('Start Date') }}" wire:model="start_date"/>

            <!-- End Date Field -->
            <flux:date-picker label="{{ __('End Date') }}" wire:model="end_date"/>

            <!-- Status Field -->
            <flux:select variant="listbox" label="{{ __('Status') }}" wire:model="status">
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
            </flux:select>

            <!-- Is Current Field -->
            <div>
                <flux:checkbox 
                    label="{{ __('Set as Current Academic Year') }}" 
                    wire:model="is_current"
                />
                <flux:text class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Setting this as current will automatically unset any other current academic year.') }}
                </flux:text>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4">
                <flux:button type="submit" class="button" variant="primary">{{ __('Create') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('create-academic-year')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</div>


