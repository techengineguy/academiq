<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Certificate;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Issue Certificate')]
class extends Component {

    public string $student_id = '';
    public string $type = 'bonafide';
    public string $issue_date = '';
    public string $purpose = '';
    public string $content = '';

    public function mount(): void
    {
        $this->issue_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function students()
    {
        return Student::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->with(['user', 'class'])
            ->orderBy('roll_number')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['required', 'in:transfer,character,bonafide,completion,other'],
            'issue_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        Certificate::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'student_id' => $validated['student_id'],
            'type' => $validated['type'],
            'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
            'issue_date' => $validated['issue_date'],
            'purpose' => $validated['purpose'] ?: null,
            'content' => $validated['content'] ?: null,
            'issued_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: __('Certificate issued successfully.'));

        $this->redirect(route('certificates.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Student') }}" variant="listbox" wire:model="student_id" searchable required>
            <flux:select.option value="">{{ __('Select Student') }}</flux:select.option>
            @foreach($this->students as $student)
                <flux:select.option value="{{ $student->id }}">
                    {{ $student->user?->first_name }} {{ $student->user?->last_name }} ({{ $student->class?->name ?? '-' }})
                </flux:select.option>
            @endforeach
        </flux:select>

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

        <flux:input label="{{ __('Purpose') }}" wire:model="purpose" placeholder="{{ __('e.g., Bank account opening, Visa application') }}" />

        <flux:textarea label="{{ __('Custom Content') }}" wire:model="content" rows="4" placeholder="{{ __('Leave blank to use default certificate text') }}" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Issue') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-certificate')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
