<?php

use Livewire\Component;
use App\Models\Staff;
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
    public $salary = '';
    public $employment_type = 'full-time';
    public $emergency_contact_name = '';
    public $emergency_contact_phone = '';
    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff|unique:users',
            'employee_id' => 'required|string|max:50|unique:staff',
            'joining_date' => 'required|date',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'employment_type' => 'required|in:permanent,temporary,contract',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
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
            'role' => 'staff',
            'status' => 'active',
        ]);

        // Create staff linked to user
        Staff::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'employee_id' => $validated['employee_id'],
            'joining_date' => $validated['joining_date'],
            'designation' => $validated['designation'],
            'department' => $validated['department'],
            'salary' => $validated['salary'] !== null && $validated['salary'] !== ''
                ? number_format((float) $validated['salary'], 2, '.', '')
                : null,
            'employment_type' => $validated['employment_type'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Staff member created successfully.'));

        $this->redirect(route('staffs.index'), navigate: true);
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
            <flux:input label="{{ __('Designation') }}" placeholder="{{ __('e.g., Accountant, Manager') }}" wire:model="designation" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Department') }}" placeholder="{{ __('e.g., Finance, HR') }}" wire:model="department" />
            <flux:input label="{{ __('Salary') }}" type="number" placeholder="{{ __('Enter salary') }}" wire:model="salary" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Employment Type') }}" variant="listbox" wire:model="employment_type" required>
                <flux:select.option value="permanent">{{ __('Permanent') }}</flux:select.option>
                <flux:select.option value="temporary">{{ __('Temporary') }}</flux:select.option>
                <flux:select.option value="contract">{{ __('Contract') }}</flux:select.option>
            </flux:select>
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Emergency Contact Name') }}" placeholder="{{ __('Name') }}" wire:model="emergency_contact_name" />
            <flux:input label="{{ __('Emergency Contact Phone') }}" placeholder="{{ __('Phone number') }}" wire:model="emergency_contact_phone" />
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-staff')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>

