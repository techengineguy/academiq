<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Template')]
class extends Component {

    public ?DocumentTemplate $template = null;

    public string $name = '';
    public string $type = 'certificate';
    public string $content = '';
    public bool $is_default = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadTemplate($id);
        }
    }

    #[On('edit-template')]
    public function loadTemplate(int $id): void
    {
        $this->template = DocumentTemplate::findOrFail($id);

        $this->name = $this->template->name;
        $this->type = $this->template->type;
        $this->content = $this->template->content;
        $this->is_default = $this->template->is_default;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:certificate,report_card,invoice,id_card,letter,other'],
            'content' => ['required', 'string'],
            'is_default' => ['boolean'],
        ]);

        $this->template->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'],
        ]);

        Flux::toast(variant: 'success', text: __('Template updated.'));

        $this->redirect(route('document-templates.index'), navigate: true);
    }
};
?>
<div>
    @if($this->template)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Template Name') }}" wire:model="name" required />

                <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
                    <flux:select.option value="certificate">{{ __('Certificate') }}</flux:select.option>
                    <flux:select.option value="report_card">{{ __('Report Card') }}</flux:select.option>
                    <flux:select.option value="invoice">{{ __('Invoice') }}</flux:select.option>
                    <flux:select.option value="id_card">{{ __('ID Card') }}</flux:select.option>
                    <flux:select.option value="letter">{{ __('Letter') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:textarea label="{{ __('Content / HTML') }}" wire:model="content" rows="12" required />

            <flux:checkbox label="{{ __('Set as Default for this type') }}" wire:model="is_default" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-template')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">{{ __('Loading...') }}</div>
    @endif
</div>
