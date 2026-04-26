<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Create Fee Types')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Create Fee Type</h1><p class="mt-4 text-gray-600">{{ __('Create fee type form') }}</p></div>

