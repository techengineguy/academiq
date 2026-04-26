<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Create Id Cards')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Create ID Card</h1><p class="mt-4 text-gray-600">{{ __('Create ID card form') }}</p></div>

