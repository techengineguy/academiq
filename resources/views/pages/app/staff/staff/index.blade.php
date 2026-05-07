<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Staff;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Staff')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function staffMembers()
    {
        return Staff::where('tenant_id', Auth::user()->tenant_id)
            ->with('user')
            ->orderBy('created_at', 'desc')->paginate(10);
    }

    public $staffIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->staffIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this staff member?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->staffIdToDelete) return;

        Staff::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->staffIdToDelete)->delete();

        $this->staffIdToDelete = null;
        unset($this->staffMembers);

        Flux::toast(variant: 'success', text: __('Staff member deleted successfully, Restore from trash.'));
    }
};
?>

<div class="space-y-6">
    <x-dialog/>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Staff') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage staff members and their information.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button class="button" x-on:click="$tsui.open.slide('create-staff')" icon="plus">
                {{ __('New Staff Member') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        @if($this->staffMembers->count())
            <flux:table :paginate="$this->staffMembers">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Employee ID') }}</flux:table.column>
                    <flux:table.column>{{ __('Designation') }}</flux:table.column>
                    <flux:table.column>{{ __('Salary') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->staffMembers as $staff)
                    <flux:table.rows>
                        <flux:table.row :key="$staff->id">
                            <flux:table.cell>{{ $staff->user?->first_name }} {{ $staff->user?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $staff->user?->email }}</flux:table.cell>
                            <flux:table.cell>{{ $staff->employee_id }}</flux:table.cell>
                            <flux:table.cell>{{ $staff->designation }}</flux:table.cell>
                            <flux:table.cell>{{ $staff->salary !== null ? number_format((float) $staff->salary, 2) : '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$staff->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($staff->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-staff'), $wire.dispatch('edit-staff', { uuid: '{{ $staff->uuid }}' })" 
                                        icon="pencil" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $staff->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Staff Members') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by adding a new staff member.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-staff" title="{{ __('Create Staff Member') }}" size="xl">
        <livewire:pages::app.staff.staff.create />
    </x-slide>

    <x-slide id="edit-staff" title="{{ __('Edit Staff Member') }}" size="xl">
        <livewire:pages::app.staff.staff.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>

