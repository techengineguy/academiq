<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Certificates')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterType = '';

    public ?int $certIdToDelete = null;

    #[Computed]
    public function certificates()
    {
        $query = Certificate::with(['student.user', 'issuedBy'])
            ->orderByDesc('issue_date');

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        return $query->paginate(15);
    }

    public function updatedFilterType(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->certIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this certificate?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->certIdToDelete) {
            return;
        }

        Certificate::findOrFail($this->certIdToDelete)
            ->delete();

        $this->certIdToDelete = null;
        unset($this->certificates);

        Flux::toast(variant: 'success', text: __('Certificate deleted.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Certificates') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Issue and manage student certificates.') }}</p>
        </div>

        <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-certificate')" icon="plus">
            {{ __('Issue Certificate') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4">
            <flux:select variant="listbox" wire:model.live="filterType" placeholder="{{ __('All Types') }}" class="max-w-xs">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="transfer">{{ __('Transfer') }}</flux:select.option>
                <flux:select.option value="character">{{ __('Character') }}</flux:select.option>
                <flux:select.option value="bonafide">{{ __('Bonafide') }}</flux:select.option>
                <flux:select.option value="completion">{{ __('Completion') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->certificates->count())
            <flux:table :paginate="$this->certificates">
                <flux:table.columns>
                    <flux:table.column>{{ __('Certificate No.') }}</flux:table.column>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Issue Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Issued By') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->certificates as $cert)
                    <flux:table.rows>
                        <flux:table.row :key="$cert->id">
                            <flux:table.cell>
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $cert->certificate_number }}</code>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $cert->student?->user?->first_name }} {{ $cert->student?->user?->last_name }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst($cert->type) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $cert->issue_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $cert->issuedBy?->first_name }} {{ $cert->issuedBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="arrow-down-tray" :href="route('certificates.download', $cert->id)" target="_blank" />
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-certificate'), $wire.dispatch('edit-certificate', { id: {{ $cert->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $cert->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Certificates') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-certificate" title="{{ __('Issue Certificate') }}" size="lg">
        <livewire:pages::app.documents.certificates.create />
    </x-slide>

    <x-slide id="edit-certificate" title="{{ __('Edit Certificate') }}" size="lg">
        <livewire:pages::app.documents.certificates.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
