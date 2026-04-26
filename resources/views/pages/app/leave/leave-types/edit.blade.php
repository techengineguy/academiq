<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Edit Leave Types')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Edit Leave Type</h1><p class="mt-4 text-gray-600">{{ __('Edit leave type form') }}</p></div>

