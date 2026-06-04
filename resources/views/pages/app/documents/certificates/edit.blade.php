<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Certificate')]
class extends Component {

    public ?Certificate $certificate = null;

    public string $type = 'bonafide';
    public string $issue_date = '';
    public string $purpose = '';
    public string $content = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadCertificate($id);
        }
    }

    #[On('edit-certificate')]
    public function loadCertificate(int $id): void
    {
        $this->certificate = Certificate::with(['student.user'])
            ->findOrFail($id);

        $this->type = $this->certificate->type;
        $this->issue_date = $this->certificate->issue_date?->format('Y-m-d') ?? '';
        $this->purpose = (string) ($this->certificate->purpose ?? '');
        $this->content = (string) ($this->certificate->content ?? '');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'type' => ['required', 'in:transfer,character,bonafide,completion,other'],
            'issue_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $this->certificate->update([
            'type' => $validated['type'],
            'issue_date' => $validated['issue_date'],
            'purpose' => $validated['purpose'] ?: null,
            'content' => $validated['content'] ?: null,
        ]);

        Flux::toast(variant: 'success', text: __('Certificate updated.'));

        $this->redirect(route('certificates.index'), navigate: true);
    }
};
?>
<div>
    @if($this->certificate)
        <div class="mb-4 rounded-lg bg-gray-50 p-3 dark:bg-zinc-800">
            <p class="text-xs text-gray-500">{{ __('Student') }}</p>
            <p class="font-semibold text-gray-900 dark:text-white">
                {{ $this->certificate->student?->user?->first_name }} {{ $this->certificate->student?->user?->last_name }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ $this->certificate->certificate_number }}</p>
        </div>

        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Certificate Type') }}" variant="listbox" wire:model="type" required>
                    <flux:select.option value="bonafide">{{ __('Bonafide') }}</flux:select.option>
                    <flux:select.option value="character">{{ __('Character') }}</flux:select.option>
                    <flux:select.option value="transfer">{{ __('Transfer') }}</flux:select.option>
                    <flux:select.option value="completion">{{ __('Completion') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                <flux:date-picker label="{{ __('Issue Date') }}" wire:model="issue_date" required />
            </div>

            <flux:input label="{{ __('Purpose') }}" wire:model="purpose" />

            <flux:textarea label="{{ __('Custom Content') }}" wire:model="content" rows="4" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-certificate')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">{{ __('Loading...') }}</div>
    @endif
</div>
