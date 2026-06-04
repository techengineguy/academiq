<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Template')]
class extends Component {

    public string $name = '';
    public string $type = 'certificate';
    public string $content = '';
    public bool $is_default = false;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:certificate,report_card,invoice,id_card,letter,other'],
            'content' => ['required', 'string'],
            'is_default' => ['boolean'],
        ]);

        DocumentTemplate::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'is_default' => $validated['is_default'],
        ]);

        Flux::toast(variant: 'success', text: __('Template created successfully.'));

        $this->redirect(route('document-templates.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Template Name') }}" wire:model="name" placeholder="{{ __('e.g., Standard Bonafide Certificate') }}" required />

            <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
                <flux:select.option value="certificate">{{ __('Certificate') }}</flux:select.option>
                <flux:select.option value="report_card">{{ __('Report Card') }}</flux:select.option>
                <flux:select.option value="invoice">{{ __('Invoice') }}</flux:select.option>
                <flux:select.option value="id_card">{{ __('ID Card') }}</flux:select.option>
                <flux:select.option value="letter">{{ __('Letter') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>
        </div>

        <div>
            <p class="text-xs text-gray-500 mb-2">
                {{ __('Use variables like') }}
                <code class="bg-zinc-100 dark:bg-zinc-700 px-1 rounded">@{{ student_name }}</code>,
                <code class="bg-zinc-100 dark:bg-zinc-700 px-1 rounded">@{{ class_name }}</code>,
                <code class="bg-zinc-100 dark:bg-zinc-700 px-1 rounded">@{{ issue_date }}</code>,
                <code class="bg-zinc-100 dark:bg-zinc-700 px-1 rounded">@{{ school_name }}</code>
            </p>
            <flux:textarea label="{{ __('Content / HTML') }}" wire:model="content" rows="12" required />
        </div>

        <flux:checkbox label="{{ __('Set as Default for this type') }}" wire:model="is_default" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-template')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
