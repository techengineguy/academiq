<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\GradeScale;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Grade Scales')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $gradeIdToDelete = null;

    #[Computed]
    public function gradeScales()
    {
        return GradeScale::orderBy('min_percentage')
            ->paginate(15);
    }

    #[Computed]
    public function totalGrades(): int
    {
        return (int) GradeScale::count();
    }

    public function confirmDelete(int $id): void
    {
        $this->gradeIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this grade scale?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->gradeIdToDelete) {
            return;
        }

        GradeScale::findOrFail($this->gradeIdToDelete)
            ->delete();

        $this->gradeIdToDelete = null;
        unset($this->gradeScales);

        Flux::toast(variant: 'success', text: __('Grade scale deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Grade Scales') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Define grading criteria with percentage ranges and grade points.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-grade-scale')" icon="plus">
            {{ __('New Grade') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->gradeScales->count())
            <flux:table :paginate="$this->gradeScales">
                <flux:table.columns>
                    <flux:table.column>{{ __('Grade') }}</flux:table.column>
                    <flux:table.column>{{ __('Min %') }}</flux:table.column>
                    <flux:table.column>{{ __('Max %') }}</flux:table.column>
                    <flux:table.column>{{ __('Grade Point') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->gradeScales as $grade)
                    <flux:table.rows>
                        <flux:table.row :key="$grade->id">
                            <flux:table.cell>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $grade->grade }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $grade->min_percentage, 2) }}%</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $grade->max_percentage, 2) }}%</flux:table.cell>
                            <flux:table.cell>{{ $grade->grade_point ? number_format((float) $grade->grade_point, 2) : '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $grade->description ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-grade-scale'), $wire.dispatch('edit-grade-scale', { id: {{ $grade->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $grade->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Grade Scales') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Define your grading system by adding grade scales.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-grade-scale" title="{{ __('Create Grade Scale') }}" size="lg">
        <livewire:pages::app.exams.grade-scales.create />
    </x-slide>

    <x-slide id="edit-grade-scale" title="{{ __('Edit Grade Scale') }}" size="lg">
        <livewire:pages::app.exams.grade-scales.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
