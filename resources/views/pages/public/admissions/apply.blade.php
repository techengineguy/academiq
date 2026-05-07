<?php

use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\ClassModel;
use App\Models\Institution;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use TallStackUi\Traits\Interactions;

new #[Title('Apply for Admission'), Layout('layouts::auth')]
class extends Component {
    use Interactions, WithFileUploads;

    public Institution $institution;

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

    public function mount(Institution $institution): void
    {
        $this->institution = $institution;
        $this->application_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('tenant_id', $this->institution->uuid),
            ],
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('tenant_id', $this->institution->uuid),
            ],
            'student_name' => ['required', 'string', 'max:255'],
            'parent_email' => ['required', 'email', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:50'],
            'application_number' => ['nullable', 'string', 'max:100', 'unique:admission_applications'],
            'application_date' => ['required', 'date'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'previous_marksheet' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'transfer_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'student_photo' => ['nullable', 'image', 'max:5120'],
            'test_marks' => ['nullable', 'numeric'],
        ]);

        $birthPath = $this->birth_certificate ? $this->birth_certificate->store('admissions/'.$this->institution->code, 'public') : null;
        $marksheetPath = $this->previous_marksheet ? $this->previous_marksheet->store('admissions/'.$this->institution->code, 'public') : null;
        $transferPath = $this->transfer_certificate ? $this->transfer_certificate->store('admissions/'.$this->institution->code, 'public') : null;
        $photoPath = $this->student_photo ? $this->student_photo->store('admissions/'.$this->institution->code, 'public') : null;

        $application = AdmissionApplication::create([
            'tenant_id' => $this->institution->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => $this->institution->id,
            'academic_year_id' => $validated['academic_year_id'],
            'class_id' => $validated['class_id'],
            'application_number' => $this->application_number ?: Str::upper('APP-'.Str::random(6)),
            'application_date' => $validated['application_date'],
            'student_name' => $validated['student_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $this->gender ?: null,
            'father_name' => $this->father_name ?: null,
            'mother_name' => $this->mother_name ?: null,
            'parent_phone' => $validated['parent_phone'],
            'parent_email' => $validated['parent_email'],
            'address' => $this->address ?: null,
            'previous_school' => $this->previous_school ?: null,
            'birth_certificate' => $birthPath,
            'previous_marksheet' => $marksheetPath,
            'transfer_certificate' => $transferPath,
            'student_photo' => $photoPath,
            'test_date' => $this->test_date ?: null,
            'test_marks' => $this->test_marks ?: null,
            'interview_date' => $this->interview_date ?: null,
            'interview_remarks' => $this->interview_remarks ?: null,
            'status' => 'pending',
        ]);

        $this->redirect(route('admissions.success', ['institution' => $this->institution->uuid, 'application' => $application->uuid]));
    }
}
?>

<div class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 lg:flex-row lg:px-6">
        <section class="flex-1 rounded-3xl bg-linear-to-br from-slate-950 via-slate-900 to-slate-800 p-8 text-white shadow-2xl ring-1 ring-white/10">
            <div class="max-w-xl space-y-6">
                <div class="inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white/80 ring-1 ring-white/15">
                    {{ $institution->name }}
                </div>
                <div>
                    <h1 class="text-4xl font-bold tracking-tight">{{ __('Admission Application') }}</h1>
                    <p class="mt-4 text-base leading-7 text-white/75">
                        {{ __('Submit an application for admission directly to :name without signing in.', ['name' => $institution->name]) }}
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                        <p class="text-sm text-white/60">{{ __('Institution Code') }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ $institution->code }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                        <p class="text-sm text-white/60">{{ __('Application Status') }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ __('Pending review') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex-1 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800 lg:p-8">
            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input label="{{ __('Student Name') }}" wire:model="student_name" required />
                    <flux:input label="{{ __('Parent Email') }}" type="email" wire:model="parent_email" required />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input label="{{ __('Parent Phone') }}" wire:model="parent_phone" required />
                    <flux:input label="{{ __('Application Number') }}" wire:model="application_number" placeholder="{{ __('Optional') }}" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model="academic_year_id" required>
                        <flux:select.option value="">{{ __('Select Academic Year') }}</flux:select.option>
                        @forelse(AcademicYear::where('tenant_id', $institution->uuid)->where('status', 'active')->get() as $year)
                            <flux:select.option value="{{ $year->id }}">{{ $year->name }}</flux:select.option>
                        @empty
                            <flux:select.option value="">{{ __('No Academic Years Available') }}</flux:select.option>
                        @endforelse
                    </flux:select>

                    <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id" required>
                        <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                        @forelse(ClassModel::where('tenant_id', $institution->uuid)->where('status', 'active')->get() as $class)
                            <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                        @empty
                            <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                        @endforelse
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:date-picker label="{{ __('Application Date') }}" wire:model="application_date" required />
                    <flux:date-picker label="{{ __('Date of Birth') }}" wire:model="date_of_birth" required />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select label="{{ __('Gender') }}" variant="listbox" wire:model="gender">
                        <flux:select.option value="">{{ __('Select Gender') }}</flux:select.option>
                        <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                        <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>
                    <flux:input label="{{ __('Previous School') }}" wire:model="previous_school" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input label="{{ __('Father Name') }}" wire:model="father_name" />
                    <flux:input label="{{ __('Mother Name') }}" wire:model="mother_name" />
                </div>

                <flux:textarea label="{{ __('Address') }}" wire:model="address" rows="4" />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input type="file" wire:model="birth_certificate" label="{{ __('Birth Certificate') }}" accept=".pdf,.jpg,.jpeg,.png" />
                    <flux:input type="file" wire:model="previous_marksheet" label="{{ __('Previous Marksheet') }}" accept=".pdf,.jpg,.jpeg,.png" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input type="file" wire:model="transfer_certificate" label="{{ __('Transfer Certificate') }}" accept=".pdf,.jpg,.jpeg,.png" />
                    <flux:input type="file" wire:model="student_photo" label="{{ __('Student Photo') }}" accept=".jpg,.jpeg,.png" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:date-picker label="{{ __('Test Date') }}" wire:model="test_date" />
                    <flux:input label="{{ __('Test Marks') }}" wire:model="test_marks" />
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <flux:date-picker label="{{ __('Interview Date') }}" wire:model="interview_date" />
                </div>

                <flux:textarea label="{{ __('Interview Remarks') }}" wire:model="interview_remarks" rows="4" />

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Fields marked required must be completed before submission.') }}</p>
                    <flux:button type="submit" class="button">{{ __('Submit Application') }}</flux:button>
                </div>
            </form>
        </section>
    </div>
