<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Leave Types')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $leaveTypeIdToDelete = null;

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::where('tenant_id', Auth::user()->tenant_id)
            ->withCount('applications')
            ->orderBy('name')
            ->paginate(15);
    }

    public function confirmDelete(int $id): void
    {
        $this->leaveTypeIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this leave type?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->leaveTypeIdToDelete) {
            return;
        }

        LeaveType::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->leaveTypeIdToDelete)
            ->delete();

        $this->leaveTypeIdToDelete = null;
        unset($this->leaveTypes);

        Flux::toast(variant: 'success', text: __('Leave type deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Leave Types') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Define leave categories available to staff and teachers.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-leave-type')" icon="plus">
            {{ __('New Leave Type') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->leaveTypes->count())
            <flux:table :paginate="$this->leaveTypes">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Max Days') }}</flux:table.column>
                    <flux:table.column>{{ __('Applicable To') }}</flux:table.column>
                    <flux:table.column>{{ __('Requires Approval') }}</flux:table.column>
                    <flux:table.column>{{ __('Applications') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->leaveTypes as $leaveType)
                    <flux:table.rows>
                        <flux:table.row :key="$leaveType->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $leaveType->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $leaveType->max_days ?? __('Unlimited') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst($leaveType->applicable_to ?? 'all') }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$leaveType->requires_approval ? 'yellow' : 'green'">
                                    {{ $leaveType->requires_approval ? __('Yes') : __('No') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="gray">{{ $leaveType->applications_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-leave-type'), $wire.dispatch('edit-leave-type', { id: {{ $leaveType->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $leaveType->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Leave Types') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create leave types like Annual, Sick, Maternity, etc.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-leave-type" title="{{ __('Create Leave Type') }}" size="lg">
        <livewire:pages::app.leave.leave-types.create />
    </x-slide>

    <x-slide id="edit-leave-type" title="{{ __('Edit Leave Type') }}" size="lg">
        <livewire:pages::app.leave.leave-types.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
