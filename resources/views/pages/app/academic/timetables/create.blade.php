<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Create Timetables')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div>
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('timetables.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Create Timetable') }}</h1>
        </div>

        <flux:card>
            <form wire:submit="save" class="space-y-6">
                <flux:input label="{{ __('Class') }}" wire:model="form.class_id" />
                <flux:input label="{{ __('Day') }}" wire:model="form.day" />
                <flux:input label="{{ __('Start Time') }}" type="time" wire:model="form.start_time" />
                <flux:input label="{{ __('End Time') }}" type="time" wire:model="form.end_time" />

                <div class="flex gap-3">
                    <flux:button type="submit">{{ __('Create') }}</flux:button>
                    <flux:button href="{{ route('timetables.index') }}" wire:navigate variant="subtle">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</div>


