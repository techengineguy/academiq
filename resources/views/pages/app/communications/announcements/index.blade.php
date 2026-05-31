<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Announcements')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterStatus = '';
    public string $filterAudience = '';

    public ?int $announcementIdToDelete = null;

    #[Computed]
    public function announcements()
    {
        $query = Announcement::where('tenant_id', Auth::user()->tenant_id)
            ->with('createdBy')
            ->orderByDesc('publish_date');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterAudience !== '') {
            $query->where('target_audience', $this->filterAudience);
        }

        return $query->paginate(15);
    }

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterAudience(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->filterAudience = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->announcementIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this announcement?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->announcementIdToDelete) {
            return;
        }

        Announcement::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->announcementIdToDelete)
            ->delete();

        $this->announcementIdToDelete = null;
        unset($this->announcements);

        Flux::toast(variant: 'success', text: __('Announcement deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Announcements') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Broadcast announcements to staff and students.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-announcement')" icon="plus">
            {{ __('New Announcement') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                <flux:select.option value="published">{{ __('Published') }}</flux:select.option>
                <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterAudience" placeholder="{{ __('All Audiences') }}">
                <flux:select.option value="">{{ __('All Audiences') }}</flux:select.option>
                <flux:select.option value="all">{{ __('Everyone') }}</flux:select.option>
                <flux:select.option value="students">{{ __('Students') }}</flux:select.option>
                <flux:select.option value="teachers">{{ __('Teachers') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
                <flux:select.option value="parents">{{ __('Parents') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->announcements->count())
            <flux:table :paginate="$this->announcements">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Audience') }}</flux:table.column>
                    <flux:table.column>{{ __('Publish Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Expiry') }}</flux:table.column>
                    <flux:table.column>{{ __('Created By') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Urgent') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->announcements as $announcement)
                    <flux:table.rows>
                        <flux:table.row :key="$announcement->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $announcement->title }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst($announcement->target_audience ?? 'all') }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $announcement->publish_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $announcement->expiry_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $announcement->createdBy?->first_name }} {{ $announcement->createdBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($announcement->status) {
                                        'published' => 'green', 'draft' => 'yellow', 'archived' => 'gray', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($announcement->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($announcement->is_urgent)
                                    <flux:badge color="red">{{ __('Urgent') }}</flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-announcement'), $wire.dispatch('edit-announcement', { id: {{ $announcement->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $announcement->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Announcements') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-announcement" title="{{ __('Create Announcement') }}" size="xl">
        <livewire:pages::app.communications.announcements.create />
    </x-slide>

    <x-slide id="edit-announcement" title="{{ __('Edit Announcement') }}" size="xl">
        <livewire:pages::app.communications.announcements.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
