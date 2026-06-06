<?php

use App\Models\AcademicYear;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\StudentScholarship;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Multitenancy\Models\Tenant;
use TallStackUi\Traits\Interactions;

new class extends Component
{
    use Interactions;

    public $student_id = '';

    public $scholarship_id = '';

    public $academic_year_id = '';

    public $discount_amount = '';

    public $granted_date = '';

    public $status = 'active';

    public $remarks = '';

    public function mount(): void
    {
        // Set current academic year as default
        $currentYear = AcademicYear::where('is_current', true)->first();
        if ($currentYear) {
            $this->academic_year_id = $currentYear->id;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'student_id' => 'required|exists:students,id',
            'scholarship_id' => 'required|exists:scholarships,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'discount_amount' => 'required|numeric|min:0',
            'granted_date' => 'required|date',
            'status' => 'required|in:active,revoked,expired',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Verify tenant isolation
        $student = Student::findOrFail($this->student_id);

        $scholarship = Scholarship::findOrFail($this->scholarship_id);

        $academicYear = AcademicYear::findOrFail($this->academic_year_id);

        StudentScholarship::create([
            'tenant_id' => Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'student_id' => $this->student_id,
            'scholarship_id' => $this->scholarship_id,
            'academic_year_id' => $this->academic_year_id,
            'discount_amount' => $this->discount_amount,
            'granted_date' => $this->granted_date,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'granted_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: __('Scholarship award created successfully.'));

        $this->redirect(route('scholarship-awards.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Student') }}" variant="listbox" wire:model="student_id" searchable required>
                <flux:select.option value="">{{ __('Select Student') }}</flux:select.option>
                @forelse(Student::orderBy('first_name')->get() as $student)
                    <flux:select.option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Students Available') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:select label="{{ __('Scholarship') }}" variant="listbox" wire:model="scholarship_id" required>
                <flux:select.option value="">{{ __('Select Scholarship') }}</flux:select.option>
                @forelse(Scholarship::where('status', 'active')->get() as $scholarship)
                    <flux:select.option value="{{ $scholarship->id }}">{{ $scholarship->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Active Scholarships') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
                <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                @forelse(AcademicYear::all() as $ay)
                    <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Academic Years') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:input label="{{ __('Discount Amount') }}" type="number" step="0.01" placeholder="{{ __('Enter amount') }}" wire:model="discount_amount" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Granted Date') }}" wire:model="granted_date" required />

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="revoked">{{ __('Revoked') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <flux:textarea label="{{ __('Remarks') }}" placeholder="{{ __('Optional remarks about this award') }}" wire:model="remarks" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" class="button" variant="primary">
                {{ __('Award Scholarship') }}
            </flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$tsui.close.slide('create-award')">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>
