<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Institution;

new
#[Title('Institutions')]
#[Layout('layouts.owner')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    #[Computed]
    public function institutions()
    {
        return Institution::withCount('users')
            ->with(['subscriptions' => fn ($q) => $q->latest()->limit(1)])
            ->when($this->search !== '', fn ($q) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('name')
            ->paginate(20);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }
};
?>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Institutions') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('All registered institutions and their subscription status.') }}</p>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by name or email...') }}"
                icon="magnifying-glass"
            />
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->institutions->count())
            <flux:table :paginate="$this->institutions">
                <flux:table.columns>
                    <flux:table.column>{{ __('Institution') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Users') }}</flux:table.column>
                    <flux:table.column>{{ __('Subscription') }}</flux:table.column>
                    <flux:table.column>{{ __('Sub Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Renews') }}</flux:table.column>
                    <flux:table.column>{{ __('Joined') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->institutions as $institution)
                    @php $latestSub = $institution->subscriptions->first(); @endphp
                    <flux:table.rows>
                        <flux:table.row :key="$institution->id">
                            <flux:table.cell>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $institution->name }}</p>
                                    @if($institution->city)
                                        <p class="text-xs text-gray-400">{{ $institution->city }}{{ $institution->state ? ', ' . $institution->state : '' }}</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-500">{{ $institution->email }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$institution->status === 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($institution->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ number_format($institution->users_count) }}</flux:table.cell>
                            <flux:table.cell>
                                @if($latestSub)
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $latestSub->plan?->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ ucfirst($latestSub->billing_cycle) }} · ₦{{ number_format((float) $latestSub->amount, 0) }}</p>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($latestSub)
                                    <flux:badge :color="match($latestSub->status) {
                                        'active'    => 'green',
                                        'trial'     => 'blue',
                                        'past_due'  => 'amber',
                                        'cancelled', 'expired' => 'red',
                                        default     => 'gray'
                                    }">{{ ucfirst($latestSub->status) }}</flux:badge>
                                @else
                                    <flux:badge color="gray">{{ __('None') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-500">
                                {{ $latestSub?->ends_at?->format('M d, Y') ?? '—' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-400">{{ $institution->created_at->format('M Y') }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="building-office-2" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Institutions Found') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Try adjusting your search or filter.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
