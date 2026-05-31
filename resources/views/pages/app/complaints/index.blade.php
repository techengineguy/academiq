<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Complaints')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';
    public string $filterPriority = '';
    public string $filterCategory = '';

    public ?int $complaintIdToDelete = null;

    #[Computed]
    public function complaints()
    {
        $query = Complaint::where('tenant_id', Auth::user()->tenant_id)
            ->with(['submittedBy', 'assignedTo'])
            ->orderByDesc('created_at');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPriority !== '') {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterCategory !== '') {
            $query->where('category', $this->filterCategory);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $base = Complaint::where('tenant_id', Auth::user()->tenant_id);

        return [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->where('status', 'open')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'resolved' => (clone $base)->where('status', 'resolved')->count(),
        ];
    }

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterPriority(): void { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->filterPriority = '';
        $this->filterCategory = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->complaintIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this complaint?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->complaintIdToDelete) {
            return;
        }

        Complaint::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->complaintIdToDelete)
            ->delete();

        $this->complaintIdToDelete = null;
        unset($this->complaints);

        Flux::toast(variant: 'success', text: __('Complaint deleted.'));
    }
};
?>
<div class="space-y-6">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Complaints') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track and resolve complaints from students and staff.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-complaint')" icon="plus">
            {{ __('New Complaint') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['total']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Open') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->stats['open']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('In Progress') }}</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->stats['in_progress']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Resolved') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->stats['resolved']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
                <flux:select.option value="in_progress">{{ __('In Progress') }}</flux:select.option>
                <flux:select.option value="resolved">{{ __('Resolved') }}</flux:select.option>
                <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterPriority" placeholder="{{ __('All Priorities') }}">
                <flux:select.option value="">{{ __('All Priorities') }}</flux:select.option>
                <flux:select.option value="urgent">{{ __('Urgent') }}</flux:select.option>
                <flux:select.option value="high">{{ __('High') }}</flux:select.option>
                <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                <flux:select.option value="low">{{ __('Low') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterCategory" placeholder="{{ __('All Categories') }}">
                <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
                <flux:select.option value="academic">{{ __('Academic') }}</flux:select.option>
                <flux:select.option value="hostel">{{ __('Hostel') }}</flux:select.option>
                <flux:select.option value="transport">{{ __('Transport') }}</flux:select.option>
                <flux:select.option value="infrastructure">{{ __('Infrastructure') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->complaints->count())
            <flux:table :paginate="$this->complaints">
                <flux:table.columns>
                    <flux:table.column>{{ __('Complaint #') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Category') }}</flux:table.column>
                    <flux:table.column>{{ __('Priority') }}</flux:table.column>
                    <flux:table.column>{{ __('Submitted By') }}</flux:table.column>
                    <flux:table.column>{{ __('Assigned To') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->complaints as $complaint)
                    <flux:table.rows>
                        <flux:table.row :key="$complaint->id">
                            <flux:table.cell>
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $complaint->complaint_number }}</code>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ Str::limit($complaint->subject, 40) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst($complaint->category) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $priorityColor = match($complaint->priority) {
                                        'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'gray', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$priorityColor">{{ ucfirst($complaint->priority) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $complaint->submittedBy?->first_name }} {{ $complaint->submittedBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $complaint->assignedTo ? $complaint->assignedTo->first_name . ' ' . $complaint->assignedTo->last_name : '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColor = match($complaint->status) {
                                        'open' => 'red', 'in_progress' => 'yellow', 'resolved' => 'green', 'closed' => 'gray', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $complaint->status)) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="eye" :href="route('complaints.show', $complaint->id)" wire:navigate />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $complaint->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Complaints') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Complaints submitted by users will appear here.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-complaint" title="{{ __('Submit Complaint') }}" size="lg">
        <livewire:pages::app.complaints.create />
    </x-slide>
</div>
