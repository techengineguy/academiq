<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Assignment;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Assignment')]
class extends Component {

    public ?Assignment $assignment = null;

    public string $title = '';
    public string $class_id = '';
    public string $section_id = '';
    public string $subject_id = '';
    public string $teacher_id = '';
    public string $description = '';
    public string $assigned_date = '';
    public string $due_date = '';
    public string $total_marks = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadAssignment($id);
        }
    }

    #[On('edit-assignment')]
    public function loadAssignment(int $id): void
    {
        $this->assignment = Assignment::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $this->title = $this->assignment->title;
        $this->class_id = (string) $this->assignment->class_id;
        $this->section_id = (string) ($this->assignment->section_id ?? '');
        $this->subject_id = (string) $this->assignment->subject_id;
        $this->teacher_id = (string) $this->assignment->teacher_id;
        $this->description = (string) ($this->assignment->description ?? '');
        $this->assigned_date = $this->assignment->assigned_date?->format('Y-m-d') ?? '';
        $this->due_date = $this->assignment->due_date?->format('Y-m-d') ?? '';
        $this->total_marks = (string) ($this->assignment->total_marks ?? '');
    }

    #[Computed]
    public function classes()
    {
        return ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->with('sections')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function sections()
    {
        if ($this->class_id === '') {
            return collect();
        }

        return Section::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $this->class_id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function subjects()
    {
        return Subject::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function teachers()
    {
        return User::where('tenant_id', Auth::user()->tenant_id)
            ->where('role', 'teacher')
            ->orderBy('first_name')
            ->get();
    }

    public function updatedClassId(): void
    {
        $this->section_id = '';
        unset($this->sections);
    }

    public function update(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'total_marks' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->assignment->update([
            'title' => $validated['title'],
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'] ?: null,
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'description' => $validated['description'] ?: null,
            'assigned_date' => $validated['assigned_date'],
            'due_date' => $validated['due_date'],
            'total_marks' => $validated['total_marks'] ?: null,
        ]);

        Flux::toast(variant: 'success', text: __('Assignment updated successfully.'));

        $this->redirect(route('assignments.index'), navigate: true);
    }
};
?>
<div>
    @if($this->assignment)
        <form wire:submit="update" class="space-y-6">
            <flux:input label="{{ __('Title') }}" wire:model="title" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Class') }}" variant="listbox" wire:model.live="class_id" required>
                    <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                    @foreach($this->classes as $class)
                        <flux:select.option value="{{ $class->id }}">
                            {{ $class->name }}@if($class->sections->count()) ({{ $class->sections->pluck('name')->join(', ') }})@endif
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select label="{{ __('Section') }}" variant="listbox" wire:model="section_id" :disabled="$class_id === ''">
                    <flux:select.option value="">{{ __('All Sections') }}</flux:select.option>
                    @foreach($this->sections as $section)
                        <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Subject') }}" variant="listbox" wire:model="subject_id" required>
                    <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
                    @foreach($this->subjects as $subject)
                        <flux:select.option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select label="{{ __('Teacher') }}" variant="listbox" wire:model="teacher_id" searchable required>
                    <flux:select.option value="">{{ __('Select Teacher') }}</flux:select.option>
                    @foreach($this->teachers as $teacher)
                        <flux:select.option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Assigned Date') }}" wire:model="assigned_date" required />
                <flux:date-picker label="{{ __('Due Date') }}" wire:model="due_date" required />
            </div>

            <flux:input label="{{ __('Total Marks') }}" type="number" wire:model="total_marks" min="1" />

            <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="4" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-assignment')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
