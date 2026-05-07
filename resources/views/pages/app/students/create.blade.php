<?php

use Livewire\Component;
use App\Models\Student;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Livewire\WithFileUploads;

new class extends Component {
    use Interactions, WithFileUploads;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $admission_number = '';
    public $admission_date = '';
    public $class_id = '';
    public $section_id = '';
    public $academic_year_id = '';
    public $roll_number = '';
    public $blood_group = '';
    public $nationality = '';
    public $religion = '';
    public $category = '';
    public $previous_school = '';
    public $birth_certificate;
    public $transfer_certificate;
    public $medical_conditions = '';
    public $allergies = '';
    public $house = '';
    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'email|unique:students|unique:users',
            'admission_number' => 'nullable|string|max:100|unique:students',
            'admission_date' => 'nullable|date',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'roll_number' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'previous_school' => 'nullable|string|max:255',
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'transfer_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'house' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $institution = Auth::user()->institution;

        // Create user first
        $user = User::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'username' => $validated['first_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['first_name']),
            'role' => 'student',
            'status' => 'active',
        ]);

        $birthPath = null;
        if ($this->birth_certificate) {
            $birthPath = $this->birth_certificate->store('students', 'public');
        }

        $transferPath = null;
        if ($this->transfer_certificate) {
            $transferPath = $this->transfer_certificate->store('students', 'public');
        }

        // Create student linked to user
        Student::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'admission_number' => $validated['admission_number'] ?? null,
            'admission_date' => $validated['admission_date'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'roll_number' => $validated['roll_number'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'category' => $validated['category'] ?? null,
            'previous_school' => $validated['previous_school'] ?? null,
            'birth_certificate' => $birthPath,
            'transfer_certificate' => $transferPath,
            'medical_conditions' => $validated['medical_conditions'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'house' => $validated['house'] ?? null,
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Student created successfully.'));

        $this->redirect(route('students.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('First Name') }}" placeholder="{{ __('Enter first name') }}" wire:model="first_name" required />
            <flux:input label="{{ __('Last Name') }}" placeholder="{{ __('Enter last name') }}" wire:model="last_name" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Email') }}" type="email" placeholder="{{ __('Enter email address') }}" wire:model="email" required />
            <flux:input label="{{ __('Admission Number') }}" placeholder="{{ __('Admission number') }}" wire:model="admission_number" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Admission Date') }}" wire:model="admission_date" />
            <flux:input label="{{ __('Roll Number') }}" placeholder="{{ __('Roll number') }}" wire:model="roll_number" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id">
                <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>

            <flux:select label="{{ __('Section') }}" variant="listbox" wire:model="section_id">
                <flux:select.option value="">{{ __('Select Section') }}</flux:select.option>
                @forelse(Section::where('tenant_id', Auth::user()->tenant_id)->get() as $section)
                    <flux:select.option value="{{ $section->id }}">{{ $section->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Sections Available') }}</flux:select.option>
                @endforelse
            </flux:select>
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
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-student')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>

