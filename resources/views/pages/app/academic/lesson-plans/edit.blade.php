<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\LessonPlan;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?LessonPlan $lessonPlan = null;

    public string $teacher_id = '';
    public string $class_id = '';
    public string $subject_id = '';
    public string $lesson_date = '';
    public string $topic = '';
    public string $objectives = '';
    public string $content = '';
    public string $teaching_method = '';
    public string $resources = '';
    public $attachment;
    public ?string $existingAttachment = null;
    public string $homework = '';
    public string $remarks = '';

    #[On('edit-lesson-plan')]
    public function loadLessonPlan(string $uuid): void
    {
        $this->lessonPlan = LessonPlan::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)->firstOrFail();

        $this->teacher_id = $this->lessonPlan->teacher_id;
        $this->class_id = $this->lessonPlan->class_id;
        $this->subject_id = $this->lessonPlan->subject_id;
        $this->lesson_date = $this->lessonPlan->lesson_date?->format('Y-m-d') ?? '';
        $this->topic = $this->lessonPlan->topic;
        $this->objectives = $this->lessonPlan->objectives;
        $this->content = $this->lessonPlan->content;
        $this->teaching_method = $this->lessonPlan->teaching_method;
        $this->resources = $this->lessonPlan->resources;
        $this->existingAttachment = $this->lessonPlan->attachment;
        $this->homework = $this->lessonPlan->homework;
        $this->remarks = $this->lessonPlan->remarks;
    }

    public function update(): void
    {
        $this->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'lesson_date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
            'resources' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,docx', 'max:10240'],
            'homework' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $attachmentPath = $this->existingAttachment;
        if ($this->attachment) {
            // Delete old attachment if it exists
            if ($this->existingAttachment) {
                Storage::disk('public')->delete($this->existingAttachment);
            }
            // Store new attachment
            $attachmentPath = $this->attachment->store('lesson-plans', 'public');
        }

        $this->lessonPlan->update([
            'teacher_id' => $this->teacher_id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'lesson_date' => $this->lesson_date,
            'topic' => $this->topic,
            'objectives' => $this->objectives,
            'content' => $this->content,
            'teaching_method' => $this->teaching_method,
            'resources' => $this->resources,
            'attachment' => $attachmentPath,
            'homework' => $this->homework,
            'remarks' => $this->remarks,
        ]);

        Flux::toast(variant: 'success', text: __('Lesson plan updated successfully.'));

        $this->redirect(route('lesson-plans.index'), navigate: true);
    }
};
?>

<div>
    @if($this->lessonPlan)
        <form wire:submit="update" class="space-y-6">
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
                <flux:input label="{{ __('Lesson Date') }}" type="date" wire:model="lesson_date" required />
            </div>

            <flux:input label="{{ __('Topic') }}" wire:model="topic" required />
            <flux:input label="{{ __('Teaching Method') }}" wire:model="teaching_method" />
            <flux:textarea label="{{ __('Objectives') }}" wire:model="objectives" />
            <flux:textarea label="{{ __('Content') }}" wire:model="content" />
            <flux:textarea label="{{ __('Resources') }}" wire:model="resources" />
            <flux:textarea label="{{ __('Homework') }}" wire:model="homework" />
            <flux:input type="file" wire:model="attachment" label="{{ __('Attachment (PDF or DOCX)') }}" accept=".pdf,.docx" />
            @if ($attachment)
                <p class="mt-2 text-sm text-blue-600">{{ __('New file:') }} {{ $attachment->getClientOriginalName() }}</p>
            @elseif ($existingAttachment)
                <p class="mt-2 text-sm text-gray-600">{{ __('Current file:') }} {{ basename($existingAttachment) }}</p>
            @endif
            <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-lesson-plan')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>


