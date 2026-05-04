<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\AcademicYear;
use Flux\Flux;

new class extends Component {
    public ?AcademicYear $academicYear = null;

    public string $name = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $status = 'active';
    public bool $is_current = false;

    #[On('edit-academic-year')]
    public function loadAcademicYear(string $uuid): void
    {
        $this->academicYear = AcademicYear::where('uuid', $uuid)->firstOrFail();

        $this->name       = $this->academicYear->name;
        $this->start_date = $this->academicYear->start_date?->format('Y-m-d') ?? '';
        $this->end_date   = $this->academicYear->end_date?->format('Y-m-d') ?? '';
        $this->status     = $this->academicYear->status;
        $this->is_current = $this->academicYear->is_current;
    }

    public function update(): void
    {
        $this->validate([
            'name'       => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'status'     => ['required', 'in:active,inactive'],
        ]);

        $this->academicYear->update([
            'name'       => $this->name,
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
            'status'     => $this->status,
            'is_current' => $this->is_current,
        ]);

        Flux::toast(variant: 'success', text: __('Academic year updated successfully.'));

        $this->dispatch('academic-year-updated');
    }
};
?>

<div>
    @if($this->academicYear)
        <form wire:submit="update" class="space-y-6">
            <flux:input
                label="{{ __('Name') }}"
                wire:model="name"
                placeholder="{{ __('e.g. 2024/2025') }}"
            />
            <flux:input
                label="{{ __('Start Date') }}"
                type="date"
                wire:model="start_date"
            />
            <flux:input
                label="{{ __('End Date') }}"
                type="date"
                wire:model="end_date"
            />
            <flux:select label="{{ __('Status') }}" wire:model="status">
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
            <flux:checkbox
                wire:model="is_current"
                label="{{ __('Set as current academic year') }}"
            />
            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-academic-year')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>