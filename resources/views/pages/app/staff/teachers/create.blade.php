<?php

use Livewire\Component;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $employee_id = '';
    public $joining_date = '';
    public $designation = '';
    public $department = '';
    public $qualification = '';
    public $specialization = '';
    public $employment_type = 'full-time';
    public $emergency_contact_name = '';
    public $emergency_contact_phone = '';
    public $emergency_contact_relation = '';
    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers|unique:users',
            'employee_id' => 'required|string|max:50|unique:teachers',
            'joining_date' => 'required|date',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:255',
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
            'role' => 'teacher',
            'status' => 'active',
        ]);

        // Create teacher linked to user
        Teacher::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'],
            'joining_date' => $validated['joining_date'],
            'designation' => $validated['designation'],
            'department' => $validated['department'],
            'qualification' => $validated['qualification'],
            'specialization' => $validated['specialization'],
            'employment_type' => $validated['employment_type'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'emergency_contact_relation' => $validated['emergency_contact_relation'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Teacher created successfully.'));

        $this->redirect(route('teachers.index'), navigate: true);
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
            <flux:input label="{{ __('Employee ID') }}" placeholder="{{ __('Enter employee ID') }}" wire:model="employee_id" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Joining Date') }}" wire:model="joining_date" required />
            <flux:input label="{{ __('Designation') }}" placeholder="{{ __('e.g., Senior Teacher, Junior Teacher') }}" wire:model="designation" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Department') }}" placeholder="{{ __('e.g., Science, English') }}" wire:model="department" />
            <flux:input label="{{ __('Qualification') }}" placeholder="{{ __('e.g., B.Sc, B.Ed') }}" wire:model="qualification" />
        </div>

        <flux:input label="{{ __('Specialization') }}" placeholder="{{ __('e.g., Physics, Mathematics') }}" wire:model="specialization" />

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Employment Type') }}" variant="listbox" wire:model="employment_type" required>
                <flux:select.option value="full-time">{{ __('Full-time') }}</flux:select.option>
                <flux:select.option value="part-time">{{ __('Part-time') }}</flux:select.option>
                <flux:select.option value="contract">{{ __('Contract') }}</flux:select.option>
            </flux:select>
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <flux:input label="{{ __('Emergency Contact Name') }}" placeholder="{{ __('Name') }}" wire:model="emergency_contact_name" />
            <flux:input label="{{ __('Emergency Contact Phone') }}" placeholder="{{ __('Phone number') }}" wire:model="emergency_contact_phone" />
            <flux:input label="{{ __('Emergency Contact Relation') }}" placeholder="{{ __('e.g., Father, Mother, Spouse') }}" wire:model="emergency_contact_relation" />
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-teacher')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
