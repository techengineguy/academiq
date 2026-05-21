<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\GradeScale;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Grade Scale')]
class extends Component {

    public ?GradeScale $gradeScale = null;

    public string $grade = '';
    public string $min_percentage = '';
    public string $max_percentage = '';
    public string $grade_point = '';
    public string $description = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadGradeScale($id);
        }
    }

    #[On('edit-grade-scale')]
    public function loadGradeScale(int $id): void
    {
        $this->gradeScale = GradeScale::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $this->grade = $this->gradeScale->grade;
        $this->min_percentage = (string) $this->gradeScale->min_percentage;
        $this->max_percentage = (string) $this->gradeScale->max_percentage;
        $this->grade_point = (string) ($this->gradeScale->grade_point ?? '');
        $this->description = (string) ($this->gradeScale->description ?? '');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'grade' => ['required', 'string', 'max:10'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_percentage'],
            'grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $this->gradeScale->update([
            'grade' => $validated['grade'],
            'min_percentage' => $validated['min_percentage'],
            'max_percentage' => $validated['max_percentage'],
            'grade_point' => $validated['grade_point'] !== '' ? $validated['grade_point'] : null,
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
        ]);

        Flux::toast(variant: 'success', text: __('Grade scale updated successfully.'));

        $this->redirect(route('grade-scales.index'), navigate: true);
    }
};
?>
<div>
    @if($this->gradeScale)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Grade') }}" wire:model="grade" required />
                <flux:input label="{{ __('Grade Point') }}" type="text" inputmode="decimal" wire:model="grade_point" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Min Percentage') }}" type="text" inputmode="decimal" wire:model="min_percentage" required />
                <flux:input label="{{ __('Max Percentage') }}" type="text" inputmode="decimal" wire:model="max_percentage" required />
            </div>

            <flux:input label="{{ __('Description') }}" wire:model="description" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-grade-scale')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
