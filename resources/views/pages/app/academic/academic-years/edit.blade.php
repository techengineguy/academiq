<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Edit Academic Years')] 
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
            <flux:button href="{{ route('academic-years.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Academic Year') }}</h1>
        </div>

        <flux:card>
            <form wire:submit="update" class="space-y-6">
                <flux:input label="{{ __('Name') }}" wire:model="form.name" />
                <flux:input label="{{ __('Start Date') }}" type="date" wire:model="form.start_date" />
                <flux:input label="{{ __('End Date') }}" type="date" wire:model="form.end_date" />

                <div class="flex gap-3">
                    <flux:button type="submit">{{ __('Update') }}</flux:button>
                    <flux:button href="{{ route('academic-years.index') }}" wire:navigate variant="subtle">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</div>


