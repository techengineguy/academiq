<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\HostelVisitor;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Log Visitor')]
class extends Component {

    public string $student_id = '';
    public string $visitor_name = '';
    public string $visitor_phone = '';
    public string $relation = '';
    public string $purpose = '';

    #[Computed]
    public function students()
    {
        return Student::where('status', 'active')
            ->whereHas('hostelAllocations', fn ($q) => $q->where('status', 'active'))
            ->with(['user', 'class'])
            ->orderBy('roll_number')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'student_id' => ['required', 'exists:students,id'],
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_phone' => ['nullable', 'string', 'max:50'],
            'relation' => ['nullable', 'string', 'max:100'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ]);

        HostelVisitor::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'student_id' => $validated['student_id'],
            'visitor_name' => $validated['visitor_name'],
            'visitor_phone' => $validated['visitor_phone'] ?: null,
            'relation' => $validated['relation'] ?: null,
            'check_in_time' => now(),
            'purpose' => $validated['purpose'] ?: null,
            'approved_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: __('Visitor logged successfully.'));

        $this->redirect(route('hostel-visitors.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Visiting Student') }}" variant="listbox" wire:model="student_id" searchable required>
            <flux:select.option value="">{{ __('Select Student') }}</flux:select.option>
            @foreach($this->students as $student)
                <flux:select.option value="{{ $student->id }}">
                    {{ $student->user?->first_name }} {{ $student->user?->last_name }} ({{ $student->class?->name ?? '-' }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Visitor Name') }}" wire:model="visitor_name" required />
            <flux:input label="{{ __('Phone') }}" wire:model="visitor_phone" />
        </div>

        <flux:input label="{{ __('Relation') }}" wire:model="relation" placeholder="{{ __('e.g., Parent, Guardian, Sibling') }}" />

        <flux:textarea label="{{ __('Purpose of Visit') }}" wire:model="purpose" rows="3" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Check In') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-visitor')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
