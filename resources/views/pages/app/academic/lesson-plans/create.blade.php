<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Create Lesson Plans')] 
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
            <flux:button href="{{ route('lesson-plans.index') }}" wire:navigate icon="arrow-left" variant="ghost" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Create Lesson Plan') }}</h1>
        </div>

        <flux:card>
            <form wire:submit="save" class="space-y-6">
                <flux:input label="{{ __('Title') }}" wire:model="form.title" />
                <flux:textarea label="{{ __('Content') }}" wire:model="form.content" />

                <div class="flex gap-3">
                    <flux:button type="submit">{{ __('Create') }}</flux:button>
                    <flux:button href="{{ route('lesson-plans.index') }}" wire:navigate variant="subtle">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</div>


