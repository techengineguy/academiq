<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

new class extends Component {
    public ?Teacher $teacher = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $employee_id = '';
    public string $joining_date = '';
    public string $designation = '';
    public string $department = '';
    public string $qualification = '';
    public string $specialization = '';
    public string $salary = '';
    public string $employment_type = 'full-time';
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relation = '';
    public string $status = 'active';

    #[On('edit-teacher')]
    public function loadTeacher(string $uuid): void
    {
        $this->teacher = Teacher::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)->firstOrFail();

        $this->first_name = $this->teacher->first_name;
        $this->last_name = $this->teacher->last_name;
        $this->email = $this->teacher->email;
        $this->employee_id = $this->teacher->employee_id;
        $this->joining_date = $this->teacher->joining_date?->format('Y-m-d') ?? '';
        $this->designation = $this->teacher->designation;
        $this->department = $this->teacher->department;
        $this->qualification = $this->teacher->qualification;
        $this->specialization = $this->teacher->specialization;
        $this->salary = (string) ($this->teacher->salary ?? '');
        $this->employment_type = $this->teacher->employment_type;
        $this->emergency_contact_name = $this->teacher->emergency_contact_name;
        $this->emergency_contact_phone = $this->teacher->emergency_contact_phone;
        $this->emergency_contact_relation = $this->teacher->emergency_contact_relation;
        $this->status = $this->teacher->status;
    }

    public function update(): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:teachers,email,' . $this->teacher->id, 'unique:users,email,' . $this->teacher->user_id],
            'employee_id' => ['required', 'string', 'max:50'],
            'joining_date' => ['required', 'date'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'employment_type' => ['required', 'in:full-time,part-time,contract'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Update user if linked
        if ($this->teacher->user_id) {
            $this->teacher->user->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
            ]);
        }

        // Update teacher
        $this->teacher->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
            'joining_date' => $this->joining_date,
            'designation' => $this->designation,
            'department' => $this->department,
            'qualification' => $this->qualification,
            'specialization' => $this->specialization,
            'salary' => $this->salary !== ''
                ? number_format((float) $this->salary, 2, '.', '')
                : null,
            'employment_type' => $this->employment_type,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relation' => $this->emergency_contact_relation,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Teacher updated successfully.'));

        $this->redirect(route('teachers.index'), navigate: true);
    }
};
?>

<div>
    @if($this->teacher)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('First Name') }}" wire:model="first_name" required />
                <flux:input label="{{ __('Last Name') }}" wire:model="last_name" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Email') }}" type="email" wire:model="email" required />
                <flux:input label="{{ __('Employee ID') }}" wire:model="employee_id" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Joining Date') }}" type="date" wire:model="joining_date" required />
                <flux:input label="{{ __('Designation') }}" wire:model="designation" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Department') }}" wire:model="department" />
                <flux:input label="{{ __('Qualification') }}" wire:model="qualification" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Specialization') }}" wire:model="specialization" />
                <flux:input label="{{ __('Salary') }}" type="text" inputmode="decimal" wire:model="salary" />
            </div>

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
                <flux:input label="{{ __('Emergency Contact Name') }}" wire:model="emergency_contact_name" />
                <flux:input label="{{ __('Emergency Contact Phone') }}" wire:model="emergency_contact_phone" />
                <flux:input label="{{ __('Emergency Contact Relation') }}" wire:model="emergency_contact_relation" />
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-teacher')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>

