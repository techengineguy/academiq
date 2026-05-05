<?php

use Livewire\Component;
use App\Models\LessonPlan;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $teacher_id = '';
    public $class_id = '';
    public $subject_id = '';
    public $lesson_date = '';
    public $topic = '';
    public $objectives = '';
    public $content = '';
    public $teaching_method = '';
    public $resources = '';
    public $attachment = '';
    public $homework = '';
    public $remarks = '';

    public function save()
    {
        $validated = $this->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'lesson_date' => 'required|date',
            'topic' => 'required|string|max:255',
            'objectives' => 'nullable|string',
            'content' => 'nullable|string',
            'teaching_method' => 'nullable|string|max:255',
            'resources' => 'nullable|string',
            'attachment' => 'nullable|string|max:255',
            'homework' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        LessonPlan::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'teacher_id' => $validated['teacher_id'],
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'],
            'lesson_date' => $validated['lesson_date'],
            'topic' => $validated['topic'],
            'objectives' => $validated['objectives'],
            'content' => $validated['content'],
            'teaching_method' => $validated['teaching_method'],
            'resources' => $validated['resources'],
            'attachment' => $validated['attachment'],
            'homework' => $validated['homework'],
            'remarks' => $validated['remarks'],
        ]);

        Flux::toast(variant: 'success', text: __('Lesson plan created successfully.'));

        $this->redirect(route('lesson-plans.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Teacher') }}" variant="listbox" wire:model="teacher_id" required>
                <flux:select.option value="">{{ __('Select Teacher') }}</flux:select.option>
                @forelse(User::where('tenant_id', Auth::user()->tenant_id)->where('role', 'teacher')->get() as $teacher)
                    <flux:select.option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Teachers Available') }}</flux:select.option>
                @endforelse
            </flux:select>
            <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id" required>
                <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Subject') }}" variant="listbox" wire:model="subject_id" required>
                <flux:select.option value="">{{ __('Select Subject') }}</flux:select.option>
                @forelse(Subject::where('tenant_id', Auth::user()->tenant_id)->get() as $subject)
                    <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Subjects Available') }}</flux:select.option>
                @endforelse
            </flux:select>
            <flux:date-picker label="{{ __('Lesson Date') }}" wire:model="lesson_date" required />
        </div>

        <flux:input label="{{ __('Topic') }}" placeholder="{{ __('Enter lesson topic') }}" wire:model="topic" required />
        <flux:input label="{{ __('Teaching Method') }}" placeholder="{{ __('e.g., Lecture, Discussion, Practical') }}" wire:model="teaching_method" />
        <flux:textarea label="{{ __('Objectives') }}" placeholder="{{ __('Enter learning objectives') }}" wire:model="objectives" />
        <flux:textarea label="{{ __('Content') }}" placeholder="{{ __('Enter lesson content') }}" wire:model="content" />
        <flux:textarea label="{{ __('Resources') }}" placeholder="{{ __('Enter required resources') }}" wire:model="resources" />
        <flux:textarea label="{{ __('Homework') }}" placeholder="{{ __('Enter homework assignments') }}" wire:model="homework" />
        <flux:input label="{{ __('Attachment') }}" placeholder="{{ __('e.g., file path or URL') }}" wire:model="attachment" />
        <flux:textarea label="{{ __('Remarks') }}" placeholder="{{ __('Enter any additional remarks') }}" wire:model="remarks" />

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-lesson-plan')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>


