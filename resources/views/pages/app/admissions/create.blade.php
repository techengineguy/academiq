<?php

use Livewire\Component;
use App\Models\AdmissionApplication;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Interactions, WithFileUploads;

    public $academic_year_id = '';
    public $class_id = '';
    public $application_number = '';
    public $application_date = '';
    public $student_name = '';
    public $date_of_birth = '';
    public $gender = '';
    public $father_name = '';
    public $mother_name = '';
    public $parent_phone = '';
    public $parent_email = '';
    public $address = '';
    public $previous_school = '';
    public $birth_certificate;
    public $previous_marksheet;
    public $transfer_certificate;
    public $student_photo;
    public $test_date;
    public $test_marks;
    public $interview_date;
    public $interview_remarks = '';
    public $status = 'pending';

    public function save()
    {
        $validated = $this->validate([
            'student_name' => 'required|string|max:255',
            'parent_email' => 'nullable|email|max:255',
            'parent_phone' => 'nullable|string|max:50',
            'application_number' => 'nullable|string|max:100|unique:admission_applications',
            'application_date' => 'nullable|date',
            'date_of_birth' => 'nullable|date',
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'previous_marksheet' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'transfer_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'student_photo' => 'nullable|image|max:5120',
            'test_marks' => 'nullable|numeric',
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        $institution = Auth::user()->institution;

        $birthPath = null;
        if ($this->birth_certificate) {
            $birthPath = $this->birth_certificate->store('admissions', 'public');
        }

        $marksheetPath = null;
        if ($this->previous_marksheet) {
            $marksheetPath = $this->previous_marksheet->store('admissions', 'public');
        }

        $transferPath = null;
        if ($this->transfer_certificate) {
            $transferPath = $this->transfer_certificate->store('admissions', 'public');
        }

        $photoPath = null;
        if ($this->student_photo) {
            $photoPath = $this->student_photo->store('admissions', 'public');
        }

        AdmissionApplication::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'academic_year_id' => $this->academic_year_id ?: null,
            'class_id' => $this->class_id ?: null,
            'application_number' => $this->application_number ?: Str::upper('APP-'.Str::random(6)),
            'application_date' => $this->application_date ?: now(),
            'student_name' => $this->student_name,
            'date_of_birth' => $this->date_of_birth ?: null,
            'gender' => $this->gender,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'parent_phone' => $this->parent_phone,
            'parent_email' => $this->parent_email,
            'address' => $this->address,
            'previous_school' => $this->previous_school,
            'birth_certificate' => $birthPath,
            'previous_marksheet' => $marksheetPath,
            'transfer_certificate' => $transferPath,
            'student_photo' => $photoPath,
            'test_date' => $this->test_date ?: null,
            'test_marks' => $this->test_marks ?: null,
            'interview_date' => $this->interview_date ?: null,
            'interview_remarks' => $this->interview_remarks,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Application created successfully.'));

        $this->redirect(route('admission-applications.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Student Name') }}" placeholder="{{ __('Applicant full name') }}" wire:model="student_name" required />
            <flux:input label="{{ __('Application Number') }}" placeholder="{{ __('Optional application number') }}" wire:model="application_number" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id">
                <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                @forelse(AcademicYear::where('tenant_id', Auth::user()->tenant_id)->get() as $ay)
                    <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Academic Years') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id">
                <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Application Date') }}" wire:model="application_date" />
            <flux:date-picker label="{{ __('Date of Birth') }}" wire:model="date_of_birth" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Parent Email') }}" type="email" wire:model="parent_email" />
            <flux:input label="{{ __('Parent Phone') }}" wire:model="parent_phone" />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <flux:textarea label="{{ __('Address') }}" wire:model="address" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Father Name') }}" wire:model="father_name" />
            <flux:input label="{{ __('Mother Name') }}" wire:model="mother_name" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input type="file" wire:model="birth_certificate" label="{{ __('Birth Certificate') }}" accept=".pdf,.jpg,.jpeg,.png" />
            <flux:input type="file" wire:model="previous_marksheet" label="{{ __('Previous Marksheet') }}" accept=".pdf,.jpg,.jpeg,.png" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input type="file" wire:model="transfer_certificate" label="{{ __('Transfer Certificate') }}" accept=".pdf,.jpg,.jpeg,.png" />
            <flux:input type="file" wire:model="student_photo" label="{{ __('Student Photo') }}" accept=".jpg,.jpeg,.png" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Test Date') }}" wire:model="test_date" />
            <flux:input label="{{ __('Test Marks') }}" wire:model="test_marks" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Interview Date') }}" wire:model="interview_date" />
            <flux:textarea label="{{ __('Interview Remarks') }}" wire:model="interview_remarks" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status">
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="accepted">{{ __('Accepted') }}</flux:select.option>
                <flux:select.option value="rejected">{{ __('Rejected') }}</flux:select.option>
            </flux:select>
            <div></div>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-admission')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>


