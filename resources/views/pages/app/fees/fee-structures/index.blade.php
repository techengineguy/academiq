<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\FeeStructure;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Fee Structures')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterAcademicYear = '';

    public ?int $structureIdToDelete = null;

    #[Computed]
    public function feeStructures()
    {
        $query = FeeStructure::with(['academicYear', 'class', 'feeType'])
            ->orderByDesc('created_at');

        if ($this->filterAcademicYear !== '') {
            $query->where('academic_year_id', $this->filterAcademicYear);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function totalStructures(): int
    {
        return (int) FeeStructure::count();
    }

    public function updatedFilterAcademicYear(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterAcademicYear = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->structureIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this fee structure?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->structureIdToDelete) {
            return;
        }

        FeeStructure::findOrFail($this->structureIdToDelete)
            ->delete();

        $this->structureIdToDelete = null;
        unset($this->feeStructures);

        Flux::toast(variant: 'success', text: __('Fee structure deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Fee Structures') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Define fee amounts per class and academic year.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-fee-structure')" icon="plus">
            {{ __('New Structure') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterAcademicYear" placeholder="{{ __('All Academic Years') }}">
                <flux:select.option value="">{{ __('All Academic Years') }}</flux:select.option>
                @foreach($this->academicYears as $year)
                    <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->feeStructures->count())
            <flux:table :paginate="$this->feeStructures">
                <flux:table.columns>
                    <flux:table.column>{{ __('Fee Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Academic Year') }}</flux:table.column>
                    <flux:table.column>{{ __('Amount') }}</flux:table.column>
                    <flux:table.column>{{ __('Frequency') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->feeStructures as $structure)
                    <flux:table.rows>
                        <flux:table.row :key="$structure->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $structure->feeType?->name ?? '-' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $structure->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $structure->academicYear?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $structure->amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst(str_replace('_', ' ', $structure->frequency)) }}</flux:table.cell>
                            <flux:table.cell>{{ $structure->due_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-fee-structure'), $wire.dispatch('edit-fee-structure', { id: {{ $structure->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $structure->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Fee Structures') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create fee structures to define amounts per class.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-fee-structure" title="{{ __('Create Fee Structure') }}" size="xl">
        <livewire:pages::app.fees.fee-structures.create />
    </x-slide>

    <x-slide id="edit-fee-structure" title="{{ __('Edit Fee Structure') }}" size="xl">
        <livewire:pages::app.fees.fee-structures.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
