<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\HostelVisitor;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Hostel Visitors')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterDate = '';

    public ?int $visitorIdToDelete = null;

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    #[Computed]
    public function visitors()
    {
        $query = HostelVisitor::where('tenant_id', Auth::user()->tenant_id)
            ->with(['student.user', 'approvedBy'])
            ->orderByDesc('check_in_time');

        if ($this->filterDate !== '') {
            $query->whereDate('check_in_time', $this->filterDate);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $base = HostelVisitor::where('tenant_id', Auth::user()->tenant_id);

        return [
            'today' => (clone $base)->whereDate('check_in_time', now())->count(),
            'checkedIn' => (clone $base)->whereNotNull('check_in_time')->whereNull('check_out_time')->count(),
        ];
    }

    public function checkOut(int $id): void
    {
        $visitor = HostelVisitor::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $visitor->update(['check_out_time' => now()]);

        Flux::toast(variant: 'success', text: __('Visitor checked out.'));
        unset($this->visitors);
    }

    public function updatedFilterDate(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterDate = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->visitorIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this visitor record?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->visitorIdToDelete) {
            return;
        }

        HostelVisitor::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->visitorIdToDelete)
            ->delete();

        $this->visitorIdToDelete = null;
        unset($this->visitors);

        Flux::toast(variant: 'success', text: __('Visitor record deleted.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Hostel Visitors') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track visitors entering and leaving the hostel.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-visitor')" icon="plus">
            {{ __('Log Visitor') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Visitors Today') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->stats['today']) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Currently Inside') }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($this->stats['checkedIn']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:date-picker wire:model.live="filterDate" />

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->visitors->count())
            <flux:table :paginate="$this->visitors">
                <flux:table.columns>
                    <flux:table.column>{{ __('Visitor') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ __('Visiting') }}</flux:table.column>
                    <flux:table.column>{{ __('Relation') }}</flux:table.column>
                    <flux:table.column>{{ __('Check In') }}</flux:table.column>
                    <flux:table.column>{{ __('Check Out') }}</flux:table.column>
                    <flux:table.column>{{ __('Approved By') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->visitors as $visitor)
                    <flux:table.rows>
                        <flux:table.row :key="$visitor->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $visitor->visitor_name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $visitor->visitor_phone ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $visitor->student?->user?->first_name }} {{ $visitor->student?->user?->last_name }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $visitor->relation ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $visitor->check_in_time?->format('M d, H:i') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($visitor->check_out_time)
                                    {{ $visitor->check_out_time->format('M d, H:i') }}
                                @else
                                    <flux:badge color="green" size="sm">{{ __('Inside') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $visitor->approvedBy?->first_name }} {{ $visitor->approvedBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    @if(! $visitor->check_out_time)
                                        <flux:button size="sm" variant="primary" class="button" wire:click="checkOut({{ $visitor->id }})">
                                            {{ __('Check Out') }}
                                        </flux:button>
                                    @endif
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $visitor->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Visitors') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-visitor" title="{{ __('Log Visitor') }}" size="lg">
        <livewire:pages::app.hostel.visitors.create />
    </x-slide>
</div>
