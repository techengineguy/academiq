<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\GradeScale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Grade Scale')]
class extends Component {

    public string $grade = '';
    public string $min_percentage = '';
    public string $max_percentage = '';
    public string $grade_point = '';
    public string $description = '';

    public function save(): void
    {
        $validated = $this->validate([
            'grade' => ['required', 'string', 'max:10'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_percentage'],
            'grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        GradeScale::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'grade' => $validated['grade'],
            'min_percentage' => $validated['min_percentage'],
            'max_percentage' => $validated['max_percentage'],
            'grade_point' => $validated['grade_point'] !== '' ? $validated['grade_point'] : null,
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
        ]);

        Flux::toast(variant: 'success', text: __('Grade scale created successfully.'));

        $this->redirect(route('grade-scales.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Grade') }}" wire:model="grade" placeholder="{{ __('e.g., A+, A, B+') }}" required />
            <flux:input label="{{ __('Grade Point') }}" type="text" inputmode="decimal" wire:model="grade_point" placeholder="{{ __('e.g., 4.0') }}" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Min Percentage') }}" type="text" inputmode="decimal" wire:model="min_percentage" placeholder="{{ __('e.g., 90') }}" required />
            <flux:input label="{{ __('Max Percentage') }}" type="text" inputmode="decimal" wire:model="max_percentage" placeholder="{{ __('e.g., 100') }}" required />
        </div>

        <flux:input label="{{ __('Description') }}" wire:model="description" placeholder="{{ __('e.g., Excellent, Very Good') }}" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-grade-scale')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
