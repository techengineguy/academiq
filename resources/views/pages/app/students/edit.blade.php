<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\TemporaryUploadedFile;
use Flux\Flux;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Student $student = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $admission_number = '';
    public string $admission_date = '';
    public string $class_id = '';
    public string $section_id = '';
    public string $academic_year_id = '';
    public string $roll_number = '';
    public string $blood_group = '';
    public string $nationality = '';
    public string $religion = '';
    public string $category = '';
    public string $previous_school = '';
    public $birth_certificate;
    public $transfer_certificate;
    public string $medical_conditions = '';
    public string $allergies = '';
    public string $house = '';
    public string $status = 'active';

    #[On('edit-student')]
    public function loadStudent(string $uuid): void
    {
        $this->student = Student::where('uuid', $uuid)->firstOrFail();

        $this->first_name = $this->student->first_name;
        $this->last_name = $this->student->last_name;
        $this->email = $this->student->email;
        $this->admission_number = (string) ($this->student->admission_number ?? '');
        $this->admission_date = $this->student->admission_date?->format('Y-m-d') ?? '';
        $this->class_id = (string) ($this->student->class_id ?? '');
        $this->section_id = (string) ($this->student->section_id ?? '');
        $this->academic_year_id = (string) ($this->student->academic_year_id ?? '');
        $this->roll_number = (string) ($this->student->roll_number ?? '');
        $this->blood_group = (string) ($this->student->blood_group ?? '');
        $this->nationality = (string) ($this->student->nationality ?? '');
        $this->religion = (string) ($this->student->religion ?? '');
        $this->category = (string) ($this->student->category ?? '');
        $this->previous_school = (string) ($this->student->previous_school ?? '');
        $this->medical_conditions = (string) ($this->student->medical_conditions ?? '');
        $this->allergies = (string) ($this->student->allergies ?? '');
        $this->house = (string) ($this->student->house ?? '');
        $this->status = $this->student->status;
        $this->birth_certificate = $this->student->birth_certificate;
        $this->transfer_certificate = $this->student->transfer_certificate;
    }

    public function update(): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['email', 'unique:students,email,' . $this->student->id, 'unique:users,email,' . $this->student->user_id],
            'admission_number' => ['nullable', 'string', 'max:100'],
            'admission_date' => ['nullable', 'date'],
            'class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'academic_year_id' => ['nullable', 'integer'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'birth_certificate' => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:10240'],
            'transfer_certificate' => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:10240'],
            'medical_conditions' => ['nullable','string'],
            'allergies' => ['nullable','string'],
            'house' => ['nullable','string','max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Update linked user if present
        if ($this->student->user_id) {
            $this->student->user->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
            ]);
        }

        $birthPath = $this->student->birth_certificate;
        if ($this->birth_certificate instanceof TemporaryUploadedFile) {
            $birthPath = $this->birth_certificate->store('students', 'public');
        }

        $transferPath = $this->student->transfer_certificate;
        if ($this->transfer_certificate instanceof TemporaryUploadedFile) {
            $transferPath = $this->transfer_certificate->store('students', 'public');
        }

        $this->student->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'admission_number' => $this->admission_number !== '' ? $this->admission_number : null,
            'admission_date' => $this->admission_date !== '' ? $this->admission_date : null,
            'class_id' => $this->class_id !== '' ? (int) $this->class_id : null,
            'section_id' => $this->section_id !== '' ? (int) $this->section_id : null,
            'academic_year_id' => $this->academic_year_id !== '' ? (int) $this->academic_year_id : null,
            'roll_number' => $this->roll_number !== '' ? $this->roll_number : null,
            'blood_group' => $this->blood_group !== '' ? $this->blood_group : null,
            'nationality' => $this->nationality !== '' ? $this->nationality : null,
            'religion' => $this->religion !== '' ? $this->religion : null,
            'category' => $this->category !== '' ? $this->category : null,
            'previous_school' => $this->previous_school !== '' ? $this->previous_school : null,
            'birth_certificate' => $birthPath,
            'transfer_certificate' => $transferPath,
            'medical_conditions' => $this->medical_conditions !== '' ? $this->medical_conditions : null,
            'allergies' => $this->allergies !== '' ? $this->allergies : null,
            'house' => $this->house !== '' ? $this->house : null,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Student updated successfully.'));

        $this->redirect(route('students.index'), navigate: true);
    }
};
?>

<div>
    @if($this->student)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('First Name') }}" wire:model="first_name" required />
                <flux:input label="{{ __('Last Name') }}" wire:model="last_name" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Email') }}" type="email" wire:model="email" required />
                <flux:input label="{{ __('Admission Number') }}" wire:model="admission_number" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Admission Date') }}" wire:model="admission_date" />
                <flux:input label="{{ __('Roll Number') }}" wire:model="roll_number" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id">
                    <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                    @forelse(ClassModel::get() as $class)
                        <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                    @empty
                        <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                    @endforelse
                </flux:select>

                <flux:select label="{{ __('Section') }}" variant="listbox" wire:model="section_id">
                    <flux:select.option value="">{{ __('Select Section') }}</flux:select.option>
                    @forelse(Section::get() as $section)
                        <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                    @empty
                        <flux:select.option value="">{{ __('No Sections Available') }}</flux:select.option>
                    @endforelse
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id">
                    <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                    @forelse(AcademicYear::get() as $ay)
                        <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                    @empty
                        <flux:select.option value="">{{ __('No Academic Years') }}</flux:select.option>
                    @endforelse
                </flux:select>
                <flux:input label="{{ __('Blood Group') }}" placeholder="{{ __('e.g., A+, O-') }}" wire:model="blood_group" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Nationality') }}" placeholder="{{ __('Nationality') }}" wire:model="nationality" />
                <flux:input label="{{ __('Religion') }}" placeholder="{{ __('Religion') }}" wire:model="religion" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Category') }}" placeholder="{{ __('Category / Caste') }}" wire:model="category" />
                <flux:input label="{{ __('Previous School') }}" placeholder="{{ __('Previous school name') }}" wire:model="previous_school" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:textarea label="{{ __('Medical Conditions') }}" placeholder="{{ __('Any medical conditions') }}" wire:model="medical_conditions" />
                <flux:textarea label="{{ __('Allergies') }}" placeholder="{{ __('Allergies if any') }}" wire:model="allergies" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('House') }}" placeholder="{{ __('House') }}" wire:model="house" />
                <flux:input type="file" wire:model="birth_certificate" label="{{ __('Birth Certificate (PDF/JPG/PNG)') }}" accept=".pdf,.jpg,.jpeg,.png" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input type="file" wire:model="transfer_certificate" label="{{ __('Transfer Certificate (PDF/JPG/PNG)') }}" accept=".pdf,.jpg,.jpeg,.png" />
                <div>
                    @if($this->birth_certificate)
                        <p class="mt-2 text-sm text-blue-600">{{ __('Current birth certificate:') }} {{ basename($this->birth_certificate) }}</p>
                    @endif
                    @if($this->transfer_certificate)
                        <p class="mt-2 text-sm text-blue-600">{{ __('Current transfer certificate:') }} {{ basename($this->transfer_certificate) }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-student')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>

