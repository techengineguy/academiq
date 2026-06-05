<?php

use App\Models\Scholarship;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

new #[Title('Scholarships')]
class extends Component
{
    use Interactions, WithPagination;

    #[Computed]
    public function scholarships()
    {
        return Scholarship::orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public $scholarshipIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->scholarshipIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this scholarship?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->scholarshipIdToDelete) {
            return;
        }

        Scholarship::findOrFail($this->scholarshipIdToDelete)->delete();

        $this->scholarshipIdToDelete = null;
        unset($this->scholarships);

        Flux::toast(variant: 'success', text: __('Scholarship deleted.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Scholarships') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage scholarships and award records.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-scholarship')" icon="plus">
                {{ __('New Scholarship') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        @if($this->scholarships->count())
            <flux:table :paginate="$this->scholarships">
                <flux:table.columns>
                    <flux:table.column>{{ __('Scholarship Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Value') }}</flux:table.column>
                    <flux:table.column>{{ __('Valid From') }}</flux:table.column>
                    <flux:table.column>{{ __('Valid To') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->scholarships as $scholarship)
                    <flux:table.rows>
                        <flux:table.row :key="$scholarship->id">
                            <flux:table.cell>{{ $scholarship->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">
                                    {{ str($scholarship->type)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($scholarship->type === 'percentage')
                                    {{ $scholarship->value }}%
                                @else
                                    {{ number_format($scholarship->value, 2) }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ optional($scholarship->valid_from)->format('Y-m-d') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ optional($scholarship->valid_to)->format('Y-m-d') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$scholarship->status === 'active' ? 'green' : 'gray'">
                                    {{ str($scholarship->status)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-scholarship'), $wire.dispatch('edit-scholarship', { uuid: '{{ $scholarship->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $scholarship->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Scholarships') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create your first scholarship to get started.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-scholarship" title="{{ __('Create Scholarship') }}" size="3xl">
        <livewire:pages::app.scholarships.create />
    </x-slide>

    <x-slide id="edit-scholarship" title="{{ __('Edit Scholarship') }}" size="3xl">
        <livewire:pages::app.scholarships.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>

