<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Listings Academic Years')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div>
    <x-toast/>
    <div>hfk</div>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Academic Years</h1>
            <flux:button href="{{ route('academic-years.create') }}" wire:navigate icon="plus">
                {{ __('New Academic Year') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Academic Years') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new academic year.') }}</p>
            </div>
        </flux:card>
    </div>
</div>

