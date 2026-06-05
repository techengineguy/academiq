<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\IdCard;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('ID Cards')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterType = '';
    public string $filterStatus = '';

    public ?int $cardIdToDelete = null;

    #[Computed]
    public function idCards()
    {
        $query = IdCard::with('user')
            ->orderByDesc('issue_date');

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    public function updatedFilterType(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->cardIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this ID card?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->cardIdToDelete) {
            return;
        }

        IdCard::findOrFail($this->cardIdToDelete)
            ->delete();

        $this->cardIdToDelete = null;
        unset($this->idCards);

        Flux::toast(variant: 'success', text: __('ID card deleted.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('ID Cards') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Generate and manage ID cards for students and staff.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-id-card')" icon="plus">
            {{ __('Generate ID Card') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterType" placeholder="{{ __('All Types') }}">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="student">{{ __('Student') }}</flux:select.option>
                <flux:select.option value="teacher">{{ __('Teacher') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
                <flux:select.option value="lost">{{ __('Lost') }}</flux:select.option>
                <flux:select.option value="damaged">{{ __('Damaged') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->idCards->count())
            <flux:table :paginate="$this->idCards">
                <flux:table.columns>
                    <flux:table.column>{{ __('Card No.') }}</flux:table.column>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Issue Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Expiry') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->idCards as $card)
                    <flux:table.rows>
                        <flux:table.row :key="$card->id">
                            <flux:table.cell>
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $card->card_number }}</code>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $card->user?->first_name }} {{ $card->user?->last_name }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$card->type === 'student' ? 'blue' : ($card->type === 'teacher' ? 'green' : 'purple')">
                                    {{ ucfirst($card->type) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $card->issue_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $card->expiry_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($card->status) {
                                        'active' => 'green', 'expired' => 'red', 'lost' => 'yellow', 'damaged' => 'orange', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($card->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="arrow-down-tray" :href="route('id-cards.download', $card->id)" target="_blank" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $card->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No ID Cards') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-id-card" title="{{ __('Generate ID Card') }}" size="lg">
        <livewire:pages::app.documents.id-cards.create />
    </x-slide>
</div>
