<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public ?Staff $staff = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $employee_id = '';
    public string $joining_date = '';
    public string $designation = '';
    public string $department = '';
    public $salary = '';
    public string $employment_type = 'full-time';
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $status = 'active';

    #[On('edit-staff')]
    public function loadStaff(string $uuid): void
    {
        $this->staff = Staff::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)->firstOrFail();

        $this->first_name = $this->staff->first_name;
        $this->last_name = $this->staff->last_name;
        $this->email = $this->staff->email;
        $this->employee_id = $this->staff->employee_id;
        $this->joining_date = $this->staff->joining_date?->format('Y-m-d') ?? '';
        $this->designation = $this->staff->designation;
        $this->department = $this->staff->department;
        $this->salary = $this->staff->salary;
        $this->employment_type = $this->staff->employment_type;
        $this->emergency_contact_name = $this->staff->emergency_contact_name;
        $this->emergency_contact_phone = $this->staff->emergency_contact_phone;
        $this->status = $this->staff->status;
    }

    public function update(): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $this->staff->user_id],
            'employee_id' => ['required', 'string', 'max:50'],
            'joining_date' => ['required', 'date'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'employment_type' => ['required', 'in:permanent,temporary,contract'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Update user if linked
        if ($this->staff->user_id) {
            $this->staff->user->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
            ]);
        }

        // Update staff
        $this->staff->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
            'joining_date' => $this->joining_date,
            'designation' => $this->designation,
            'department' => $this->department,
            'salary' => $this->salary !== ''
                ? number_format((float) $this->salary, 2, '.', '')
                : null,
            'employment_type' => $this->employment_type,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Staff member updated successfully.'));

        $this->redirect(route('staffs.index'), navigate: true);
    }
};
?>

<div>
    @if($this->staff)
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
                <flux:input label="{{ __('Salary') }}" type="number" wire:model="salary" />
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
                <flux:input label="{{ __('Emergency Contact Name') }}" wire:model="emergency_contact_name" />
                <flux:input label="{{ __('Emergency Contact Phone') }}" wire:model="emergency_contact_phone" />
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-staff')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>

