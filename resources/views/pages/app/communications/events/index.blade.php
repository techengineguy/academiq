<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Listings Events')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Listings Events') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage records and activity for this section.') }}</p><p class="mt-4 text-gray-600">{{ __('Events page') }}</p></div>

