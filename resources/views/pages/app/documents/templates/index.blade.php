<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Document Templates')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $templateIdToDelete = null;

    #[Computed]
    public function templates()
    {
        return DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);
    }

    public function confirmDelete(int $id): void
    {
        $this->templateIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this template?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->templateIdToDelete) {
            return;
        }

        DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->templateIdToDelete)
            ->delete();

        $this->templateIdToDelete = null;
        unset($this->templates);

        Flux::toast(variant: 'success', text: __('Template deleted.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Document Templates') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Create reusable templates for certificates, letters, and more.') }}</p>
        </div>

        <flux:button class="button" x-on:click="$tsui.open.slide('create-template')" icon="plus">
            {{ __('New Template') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->templates->count())
            <flux:table :paginate="$this->templates">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Default') }}</flux:table.column>
                    <flux:table.column>{{ __('Variables') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->templates as $template)
                    <flux:table.rows>
                        <flux:table.row :key="$template->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst(str_replace('_', ' ', $template->type)) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($template->is_default)
                                    <flux:badge color="green">{{ __('Default') }}</flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($template->variables)
                                    <span class="text-xs text-gray-500">{{ implode(', ', array_keys($template->variables)) }}</span>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="subtle" icon="square-pen" x-on:click="$tsui.open.slide('edit-template'), $wire.dispatch('edit-template', { id: {{ $template->id }} })" />
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $template->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Templates') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Create templates to speed up document generation.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-template" title="{{ __('Create Template') }}" size="xl">
        <livewire:pages::app.documents.templates.create />
    </x-slide>

    <x-slide id="edit-template" title="{{ __('Edit Template') }}" size="xl">
        <livewire:pages::app.documents.templates.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
