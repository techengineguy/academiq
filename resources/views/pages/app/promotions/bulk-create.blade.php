<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentPromotion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class extends Component
{
    use Interactions;

    public $studentIds = [];

    public $to_class_id = '';

    public $to_section_id = '';

    public $to_academic_year_id = '';

    public $status = 'promoted';

    public $remarks = '';

    public function mount($studentIds = [])
    {
        $this->studentIds = $studentIds ?? [];
    }

    public function updatedToClassId(): void
    {
        // Reset section whenever class changes
        $this->to_section_id = '';
    }

    public function getSectionsProperty()
    {
        if (! $this->to_class_id) {
            return collect();
        }

        return Section::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $this->to_class_id)
            ->get();
    }

    public function save()
    {
        $this->validate([
            'to_class_id' => 'required|exists:classes,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'to_section_id' => 'nullable|exists:sections,id',
            'status' => 'required|in:promoted,detained,transferred',
            'remarks' => 'nullable|string|max:500',
        ]);

        if (empty($this->studentIds)) {
            Flux::toast(variant: 'warning', text: __('No students selected.'));

            return;
        }

        $students = Student::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('id', $this->studentIds)
            ->get();

        // Verify tenant isolation once for shared target values.
        ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->to_class_id);

        AcademicYear::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->to_academic_year_id);

        $createdCount = 0;

        foreach ($students as $student) {
            DB::transaction(function () use ($student): void {
                // Create promotion record for each student.
                StudentPromotion::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'uuid' => Str::uuid(),
                    'student_id' => $student->id,
                    'from_class_id' => $student->class_id,
                    'to_class_id' => $this->to_class_id,
                    'from_section_id' => $student->section_id,
                    'to_section_id' => $this->to_section_id ?: null,
                    'from_academic_year_id' => $student->academic_year_id,
                    'to_academic_year_id' => $this->to_academic_year_id,
                    'status' => $this->status,
                    'remarks' => $this->remarks,
                    'processed_by' => Auth::id(),
                ]);

                // Update student's current placement.
                $student->update([
                    'class_id' => $this->to_class_id,
                    'section_id' => $this->to_section_id ?: null,
                    'academic_year_id' => $this->to_academic_year_id,
                ]);
            });

            $createdCount++;
        }

        Flux::toast(variant: 'success', text: __('Promoted '.$createdCount.' student(s) successfully.'));

        $this->redirect(route('promotions.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-900">
            <p class="text-sm font-medium text-blue-900 dark:text-blue-200">
                {{ __('Promoting') }} <strong>{{ count($studentIds) }}</strong> {{ __('student(s)') }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('To Class') }}" variant="listbox" wire:model.live="to_class_id" required>
                <flux:select.option value="">{{ __('Select To Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:select label="{{ __('To Academic Year') }}" variant="listbox" wire:model="to_academic_year_id" required>
                <flux:select.option value="">{{ __('Select To Year') }}</flux:select.option>
                @forelse(AcademicYear::where('tenant_id', Auth::user()->tenant_id)->get() as $ay)
                    <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Academic Years') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('To Section') }}" variant="listbox" wire:model="to_section_id" :disabled="!$to_class_id">
                <flux:select.option value="">
                    {{ $to_class_id ? __('Select To Section') : __('Select a class first') }}
                </flux:select.option>
                @foreach($this->sections as $section)
                    <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="promoted">{{ __('Promoted') }}</flux:select.option>
                <flux:select.option value="detained">{{ __('Detained') }}</flux:select.option>
                <flux:select.option value="transferred">{{ __('Transferred') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <flux:textarea label="{{ __('Remarks') }}" placeholder="{{ __('Optional remarks for this bulk promotion') }}" wire:model="remarks" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" class="button" variant="primary">
                {{ __('Promote All') }} ({{ count($studentIds) }})
            </flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$tsui.close.slide('bulk-promote')">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>