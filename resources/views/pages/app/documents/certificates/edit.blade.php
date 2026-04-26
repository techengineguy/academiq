<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Edit Certificates')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Edit Certificate</h1><p class="mt-4 text-gray-600">{{ __('Edit certificate form') }}</p></div>

