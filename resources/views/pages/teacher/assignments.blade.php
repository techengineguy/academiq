<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Assignment;
use App\Models\ClassSubject;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new
#[Title('My Assignments')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;
    use Interactions;

    // Create form
    public string $title = '';
    public string $class_id = '';
    public string $section_id = '';
    public string $subject_id = '';
    public string $description = '';
    public string $assigned_date = '';
    public string $due_date = '';
    public string $total_marks = '';

    public ?int $assignmentIdToDelete = null;

    public function mount(): void
    {
        $this->assigned_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function assignments()
    {
        return Assignment::where('teacher_id', Auth::id())
            ->with(['class', 'subject', 'section'])
            ->withCount('submissions')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function myClasses()
    {
        $classIds = ClassSubject::where('teacher_id', Auth::id())
            ->pluck('class_id')
            ->unique();

        return ClassModel::whereIn('id', $classIds)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function sections()
    {
        if ($this->class_id === '') {
            return collect();
        }

        return Section::where('class_id', $this->class_id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function subjects()
    {
        if ($this->class_id === '') {
            return collect();
        }

        $subjectIds = ClassSubject::where('teacher_id', Auth::id())
            ->where('class_id', $this->class_id)
            ->pluck('subject_id');

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
    }

    public function updatedClassId(): void
    {
        $this->section_id = '';
        $this->subject_id = '';
        unset($this->sections, $this->subjects);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'description' => ['nullable', 'string'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'total_marks' => ['nullable', 'integer', 'min:1'],
        ]);

        Assignment::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'teacher_id' => Auth::id(),
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'] ?: null,
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'assigned_date' => $validated['assigned_date'],
            'due_date' => $validated['due_date'],
            'total_marks' => $validated['total_marks'] ?: null,
            'status' => 'active',
        ]);

        Flux::toast(variant: 'success', text: __('Assignment created successfully.'));

        $this->reset(['title', 'class_id', 'section_id', 'subject_id', 'description', 'due_date', 'total_marks']);
        $this->assigned_date = now()->format('Y-m-d');
        unset($this->assignments);

        $this->redirect(route('teacher.assignments'), navigate: true);
    }

    public function confirmDelete(int $id): void
    {
        $this->assignmentIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this assignment?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->assignmentIdToDelete) {
            return;
        }

        Assignment::where('teacher_id', Auth::id())
            ->findOrFail($this->assignmentIdToDelete)
            ->delete();

        $this->assignmentIdToDelete = null;
        unset($this->assignments);

        Flux::toast(variant: 'success', text: __('Assignment deleted.'));
    }
};
?>
<div>
<x-dialog />
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Assignments') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Create and manage assignments for your classes.') }}</p>
        </div>
        <flux:button class="button" x-on:click="$tsui.open.slide('create-assignment')" icon="plus">
            {{ __('New Assignment') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->assignments->count())
            <flux:table :paginate="$this->assignments">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Submissions') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->assignments as $assignment)
                    <flux:table.rows>
                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $assignment->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->due_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ $assignment->submissions_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($assignment->due_date?->isPast())
                                    <flux:badge color="red">{{ __('Closed') }}</flux:badge>
                                @else
                                    <flux:badge color="green">{{ __('Active') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $assignment->id }})" />
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Assignments') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Create your first assignment.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-assignment" title="{{ __('Create Assignment') }}" size="xl">
        <form wire:submit="save" class="space-y-6">
            <flux:input label="{{ __('Title') }}" wire:model="title" placeholder="{{ __('e.g., Chapter 5 Exercise') }}" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Class') }}" variant="listbox" wire:model.live="class_id" required>
                    <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                    @foreach($this->myClasses as $class)
                        <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select label="{{ __('Section') }}" variant="listbox" wire:model="section_id" :disabled="$class_id === ''">
                    <flux:select.option value="">{{ __('All Sections') }}</flux:select.option>
                    @foreach($this->sections as $section)
                        <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:select label="{{ __('Subject') }}" variant="listbox" wire:model="subject_id" :disabled="$class_id === ''" required>
                <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
                @foreach($this->subjects as $subject)
                    <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Assigned Date') }}" wire:model="assigned_date" required />
                <flux:date-picker label="{{ __('Due Date') }}" wire:model="due_date" required />
            </div>

            <flux:input label="{{ __('Total Marks') }}" type="number" wire:model="total_marks" min="1" />

            <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="4" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-assignment')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-slide>
</div>
</div>
