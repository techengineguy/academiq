<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\FeeType;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Fee Types')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $feeTypeIdToDelete = null;

    #[Computed]
    public function feeTypes()
    {
        return FeeType::orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function totalFeeTypes(): int
    {
        return (int) FeeType::count();
    }

    public function confirmDelete(int $id): void
    {
        $this->feeTypeIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this fee type?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->feeTypeIdToDelete) {
            return;
        }

        FeeType::findOrFail($this->feeTypeIdToDelete)
            ->delete();

        $this->feeTypeIdToDelete = null;
        unset($this->feeTypes);

        Flux::toast(variant: 'success', text: __('Fee type deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Fee Types') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Define the types of fees charged to students.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-fee-type')" icon="plus">
            {{ __('New Fee Type') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->feeTypes->count())
            <flux:table :paginate="$this->feeTypes">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Code') }}</flux:table.column>
                    <flux:table.column>{{ __('Refundable') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->feeTypes as $feeType)
                    <flux:table.rows>
                        <flux:table.row :key="$feeType->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $feeType->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $feeType->code }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$feeType->is_refundable ? 'green' : 'gray'">
                                    {{ $feeType->is_refundable ? __('Yes') : __('No') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$feeType->status === 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($feeType->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ Str::limit($feeType->description, 40) ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-fee-type'), $wire.dispatch('edit-fee-type', { id: {{ $feeType->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $feeType->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Fee Types') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Define fee types like Tuition, Transport, Lab Fee, etc.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-fee-type" title="{{ __('Create Fee Type') }}" size="lg">
        <livewire:pages::app.fees.fee-types.create />
    </x-slide>

    <x-slide id="edit-fee-type" title="{{ __('Edit Fee Type') }}" size="lg">
        <livewire:pages::app.fees.fee-types.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
