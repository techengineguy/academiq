<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\LessonPlan;
use App\Models\ClassSubject;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new
#[Title('Lesson Plans')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;
    use Interactions;

    // Create form
    public string $topic = '';
    public string $class_id = '';
    public string $subject_id = '';
    public string $lesson_date = '';
    public string $objectives = '';
    public string $content = '';
    public string $teaching_method = '';
    public string $resources = '';
    public string $homework = '';
    public string $remarks = '';

    public ?int $planIdToDelete = null;

    public function mount(): void
    {
        $this->lesson_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function lessonPlans()
    {
        return LessonPlan::where('tenant_id', Auth::user()->tenant_id)
            ->where('teacher_id', Auth::id())
            ->with(['class', 'subject'])
            ->orderByDesc('lesson_date')
            ->paginate(10);
    }

    #[Computed]
    public function myClasses()
    {
        $classIds = ClassSubject::where('tenant_id', Auth::user()->tenant_id)
            ->where('teacher_id', Auth::id())
            ->pluck('class_id')
            ->unique();

        return ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function subjects()
    {
        if ($this->class_id === '') {
            return collect();
        }

        $subjectIds = ClassSubject::where('tenant_id', Auth::user()->tenant_id)
            ->where('teacher_id', Auth::id())
            ->where('class_id', $this->class_id)
            ->pluck('subject_id');

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
    }

    public function updatedClassId(): void
    {
        $this->subject_id = '';
        unset($this->subjects);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'topic' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'lesson_date' => ['required', 'date'],
            'objectives' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
            'resources' => ['nullable', 'string'],
            'homework' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        LessonPlan::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'teacher_id' => Auth::id(),
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'],
            'topic' => $validated['topic'],
            'lesson_date' => $validated['lesson_date'],
            'objectives' => $validated['objectives'] ?: null,
            'content' => $validated['content'] ?: null,
            'teaching_method' => $validated['teaching_method'] ?: null,
            'resources' => $validated['resources'] ?: null,
            'homework' => $validated['homework'] ?: null,
            'remarks' => $validated['remarks'] ?: null,
        ]);

        Flux::toast(variant: 'success', text: __('Lesson plan created successfully.'));

        $this->reset(['topic', 'class_id', 'subject_id', 'objectives', 'content', 'teaching_method', 'resources', 'homework', 'remarks']);
        $this->lesson_date = now()->format('Y-m-d');
        unset($this->lessonPlans);

        $this->redirect(route('teacher.lesson-plans'), navigate: true);
    }

    public function confirmDelete(int $id): void
    {
        $this->planIdToDelete = $id;

        $this->dialog()
            ->question(__('Delete this lesson plan?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->planIdToDelete) {
            return;
        }

        LessonPlan::where('tenant_id', Auth::user()->tenant_id)
            ->where('teacher_id', Auth::id())
            ->findOrFail($this->planIdToDelete)
            ->delete();

        $this->planIdToDelete = null;
        unset($this->lessonPlans);

        Flux::toast(variant: 'success', text: __('Lesson plan deleted.'));
    }
};
?>
<div>
<x-dialog />
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Lesson Plans') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Plan and document your lessons.') }}</p>
        </div>
        <flux:button class="button" x-on:click="$tsui.open.slide('create-lesson-plan')" icon="plus">
            {{ __('New Lesson Plan') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->lessonPlans->count())
            <flux:table :paginate="$this->lessonPlans">
                <flux:table.columns>
                    <flux:table.column>{{ __('Topic') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Method') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->lessonPlans as $plan)
                    <flux:table.rows>
                        <flux:table.row :key="$plan->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $plan->topic }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $plan->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $plan->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $plan->lesson_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $plan->teaching_method ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $plan->id }})" />
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Lesson Plans') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Create your first lesson plan.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-lesson-plan" title="{{ __('Create Lesson Plan') }}" size="xl">
        <form wire:submit="save" class="space-y-6">
            <flux:input label="{{ __('Topic') }}" wire:model="topic" placeholder="{{ __('e.g., Introduction to Algebra') }}" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Class') }}" variant="listbox" wire:model.live="class_id" required>
                    <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                    @foreach($this->myClasses as $class)
                        <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select label="{{ __('Subject') }}" variant="listbox" wire:model="subject_id" :disabled="$class_id === ''" required>
                    <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
                    @foreach($this->subjects as $subject)
                        <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Lesson Date') }}" wire:model="lesson_date" required />
                <flux:input label="{{ __('Teaching Method') }}" wire:model="teaching_method" placeholder="{{ __('e.g., Lecture, Discussion') }}" />
            </div>

            <flux:textarea label="{{ __('Objectives') }}" wire:model="objectives" rows="3" placeholder="{{ __('What students will learn...') }}" />

            <flux:textarea label="{{ __('Content / Notes') }}" wire:model="content" rows="4" />

            <flux:textarea label="{{ __('Resources') }}" wire:model="resources" rows="2" placeholder="{{ __('Textbooks, materials...') }}" />

            <flux:textarea label="{{ __('Homework') }}" wire:model="homework" rows="2" />

            <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="2" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-lesson-plan')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-slide>
</div>
</div>
