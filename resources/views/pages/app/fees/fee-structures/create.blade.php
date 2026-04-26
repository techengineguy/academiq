<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Create Fee Structures')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Create Fee Structure</h1><p class="mt-4 text-gray-600">{{ __('Create fee structure form') }}</p></div>

