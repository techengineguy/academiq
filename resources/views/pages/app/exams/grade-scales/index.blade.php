<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Listings Grade Scales')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Grade Scales</h1><p class="mt-4 text-gray-600">{{ __('Grade scales page') }}</p></div>

