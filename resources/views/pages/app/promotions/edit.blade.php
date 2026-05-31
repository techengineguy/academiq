<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentPromotion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class extends Component
{
    use Interactions;

    public ?StudentPromotion $promotion = null;

    public $student_id = '';

    public $from_class_id = '';

    public $to_class_id = '';

    public $from_section_id = '';

    public $to_section_id = '';

    public $from_academic_year_id = '';

    public $to_academic_year_id = '';

    public $status = 'promoted';

    public $remarks = '';

    #[On('edit-promotion')]
    public function loadPromotion(string $uuid)
    {
        $this->promotion = StudentPromotion::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $this->student_id = $this->promotion->student_id;
        $this->from_class_id = $this->promotion->from_class_id;
        $this->to_class_id = $this->promotion->to_class_id;
        $this->from_section_id = $this->promotion->from_section_id;
        $this->to_section_id = $this->promotion->to_section_id;
        $this->from_academic_year_id = $this->promotion->from_academic_year_id;
        $this->to_academic_year_id = $this->promotion->to_academic_year_id;
        $this->status = $this->promotion->status;
        $this->remarks = $this->promotion->remarks;
    }

    public function updatedFromClassId(): void
    {
        $this->from_section_id = '';
    }

    public function updatedToClassId(): void
    {
        $this->to_section_id = '';
    }

    public function getFromSectionsProperty()
    {
        if (!$this->from_class_id) {
            return collect();
        }

        return Section::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $this->from_class_id)
            ->get();
    }

    public function getToSectionsProperty()
    {
        if (!$this->to_class_id) {
            return collect();
        }

        return Section::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $this->to_class_id)
            ->get();
    }

    public function update()
    {
        $this->validate([
            'student_id' => 'required|exists:students,id',
            'from_class_id' => 'required|exists:classes,id',
            'to_class_id' => 'required|exists:classes,id|different:from_class_id',
            'from_section_id' => 'nullable|exists:sections,id',
            'to_section_id' => 'nullable|exists:sections,id',
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'status' => 'required|in:promoted,detained,transferred',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Verify tenant isolation
        Student::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->student_id);

        ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->from_class_id);

        ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->to_class_id);

        AcademicYear::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->from_academic_year_id);

        AcademicYear::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->to_academic_year_id);

        DB::transaction(function (): void {
            $this->promotion->update([
                'student_id' => $this->student_id,
                'from_class_id' => $this->from_class_id,
                'to_class_id' => $this->to_class_id,
                'from_section_id' => $this->from_section_id ?: null,
                'to_section_id' => $this->to_section_id ?: null,
                'from_academic_year_id' => $this->from_academic_year_id,
                'to_academic_year_id' => $this->to_academic_year_id,
                'status' => $this->status,
                'remarks' => $this->remarks,
            ]);

            Student::where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($this->student_id)
                ->update([
                    'class_id' => $this->to_class_id,
                    'section_id' => $this->to_section_id ?: null,
                    'academic_year_id' => $this->to_academic_year_id,
                ]);
        });

        Flux::toast(variant: 'success', text: __('Promotion updated.'));

        $this->redirect(route('promotions.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="update" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Student') }}" variant="listbox" wire:model="student_id" searchable required>
                <flux:select.option value="">{{ __('Select Student') }}</flux:select.option>
                @forelse(Student::where('tenant_id', Auth::user()->tenant_id)->orderBy('first_name')->get() as $student)
                    <flux:select.option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Students Available') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="promoted">{{ __('Promoted') }}</flux:select.option>
                <flux:select.option value="detained">{{ __('Detained') }}</flux:select.option>
                <flux:select.option value="transferred">{{ __('Transferred') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('From Class') }}" variant="listbox" wire:model.live="from_class_id" required>
                <flux:select.option value="">{{ __('Select From Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:select label="{{ __('To Class') }}" variant="listbox" wire:model.live="to_class_id" required>
                <flux:select.option value="">{{ __('Select To Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('From Section') }}" variant="listbox" wire:model="from_section_id" :disabled="!$from_class_id">
                <flux:select.option value="">
                    {{ $from_class_id ? __('Select From Section') : __('Select a class first') }}
                </flux:select.option>
                @foreach($this->fromSections as $section)
                    <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select label="{{ __('To Section') }}" variant="listbox" wire:model="to_section_id" :disabled="!$to_class_id">
                <flux:select.option value="">
                    {{ $to_class_id ? __('Select To Section') : __('Select a class first') }}
                </flux:select.option>
                @foreach($this->toSections as $section)
                    <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('From Academic Year') }}" variant="listbox" wire:model="from_academic_year_id" required>
                <flux:select.option value="">{{ __('Select From Year') }}</flux:select.option>
                @forelse(AcademicYear::where('tenant_id', Auth::user()->tenant_id)->get() as $ay)
                    <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Academic Years') }}</flux:select.option>
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

        <div class="grid grid-cols-1 gap-4">
            <flux:textarea label="{{ __('Remarks') }}" placeholder="{{ __('Optional remarks about this promotion') }}" wire:model="remarks" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" class="button" variant="primary">
                {{ __('Update Promotion') }}
            </flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$tsui.close.slide('edit-promotion')">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>