<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Create Payroll')] 
class extends Component {
    public $id;

    public function mount($id = null)
    {
        if($id) $this->id = $id;
    }
};
?>

<div><h1 class="text-2xl font-bold">Create Payroll</h1><p class="mt-4 text-gray-600">{{ __('Create payroll form') }}</p></div>

