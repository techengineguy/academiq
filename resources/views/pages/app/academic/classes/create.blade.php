<?php

use Livewire\Component;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $name = '';
    public $code = '';
    public $academic_year_id = '';
    public $capacity = '';
    public $status = 'active';

    public function mount()
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        if ($currentYear) {
            $this->academic_year_id = $currentYear->id;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes',
            'academic_year_id' => 'required|exists:academic_years,id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $institution = Tenant::current();

        ClassModel::create([
            'tenant_id' => Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'academic_year_id' => $validated['academic_year_id'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Class created successfully.'));

        $this->redirect(route('classes.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Name') }}" placeholder="{{ __('Enter class name') }}" wire:model="name" required />
        <flux:input label="{{ __('Code') }}" placeholder="{{ __('Enter class code') }}" wire:model="code" required />
        <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
            <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
            @forelse(AcademicYear::where('status', 'active')->get() as $year)
                <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
            @empty
                <flux:select.option value="">{{ __('No Academic Years Available') }}</flux:select.option>
            @endforelse
        </flux:select>
        <flux:input label="{{ __('Capacity') }}" placeholder="{{ __('Enter class capacity') }}" wire:model="capacity" type="number" min="1" required />
        <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
        </flux:select>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-class')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>


