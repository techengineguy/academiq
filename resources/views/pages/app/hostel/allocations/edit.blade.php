<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Edit Allocations')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Edit Hostel Allocation</h1><p class="mt-4 text-gray-600">{{ __('Edit hostel allocation form') }}</p></div>

