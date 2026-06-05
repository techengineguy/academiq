<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Leave Applications')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';
    public string $filterRole = '';

    public ?int $applicationIdToDelete = null;

    #[Computed]
    public function applications()
    {
        $query = LeaveApplication::with(['user', 'leaveType', 'approvedBy'])
            ->orderByDesc('created_at');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterRole !== '') {
            $query->whereHas('user', fn ($q) => $q->where('role', $this->filterRole));
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $base = LeaveApplication::query();

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];
    }

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterRole(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->filterRole = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->applicationIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this leave application?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->applicationIdToDelete) {
            return;
        }

        LeaveApplication::findOrFail($this->applicationIdToDelete)
            ->delete();

        $this->applicationIdToDelete = null;
        unset($this->applications);

        Flux::toast(variant: 'success', text: __('Application deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Leave Applications') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Review and manage staff leave requests.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-leave-application')" icon="plus">
            {{ __('New Application') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['total']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Pending') }}</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->stats['pending']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Approved') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->stats['approved']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Rejected') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->stats['rejected']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="approved">{{ __('Approved') }}</flux:select.option>
                <flux:select.option value="rejected">{{ __('Rejected') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterRole" placeholder="{{ __('All Roles') }}">
                <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                <flux:select.option value="teacher">{{ __('Teachers') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->applications->count())
            <flux:table :paginate="$this->applications">
                <flux:table.columns>
                    <flux:table.column>{{ __('Employee') }}</flux:table.column>
                    <flux:table.column>{{ __('Leave Type') }}</flux:table.column>
                    <flux:table.column>{{ __('From') }}</flux:table.column>
                    <flux:table.column>{{ __('To') }}</flux:table.column>
                    <flux:table.column>{{ __('Days') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->applications as $application)
                    <flux:table.rows>
                        <flux:table.row :key="$application->id">
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $application->user?->first_name }} {{ $application->user?->last_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ ucfirst($application->user?->role ?? '-') }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $application->leaveType?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $application->start_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $application->end_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $application->total_days }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($application->status) {
                                        'approved' => 'green',
                                        'pending' => 'yellow',
                                        'rejected' => 'red',
                                        default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($application->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-leave-application'), $wire.dispatch('edit-leave-application', { id: {{ $application->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $application->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Applications') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Leave applications will appear here.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-leave-application" title="{{ __('New Leave Application') }}" size="lg">
        <livewire:pages::app.leave.applications.create />
    </x-slide>

    <x-slide id="edit-leave-application" title="{{ __('Review Application') }}" size="lg">
        <livewire:pages::app.leave.applications.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
