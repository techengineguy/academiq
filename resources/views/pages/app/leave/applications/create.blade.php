<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Leave Application')]
class extends Component {

    public string $user_id = '';
    public string $leave_type_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';

    #[Computed]
    public function employees()
    {
        return User::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('role', ['teacher', 'staff'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('name')
            ->get();
    }

    public function totalDays(): int
    {
        if ($this->start_date === '' || $this->end_date === '') {
            return 0;
        }

        try {
            return (int) now()->parse($this->start_date)->diffInDays(now()->parse($this->end_date)) + 1;
        } catch (\Exception) {
            return 0;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $totalDays = (int) now()->parse($validated['start_date'])->diffInDays(now()->parse($validated['end_date'])) + 1;

        LeaveApplication::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'user_id' => $validated['user_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        Flux::toast(variant: 'success', text: __('Leave application submitted successfully.'));

        $this->redirect(route('leave-applications.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Employee') }}" variant="listbox" wire:model="user_id" searchable required>
            <flux:select.option value="">{{ __('Select Employee') }}</flux:select.option>
            @foreach($this->employees as $employee)
                <flux:select.option value="{{ $employee->id }}">
                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ ucfirst($employee->role) }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="{{ __('Leave Type') }}" variant="listbox" wire:model="leave_type_id" required>
            <flux:select.option value="">{{ __('Select Leave Type') }}</flux:select.option>
            @foreach($this->leaveTypes as $leaveType)
                <flux:select.option value="{{ $leaveType->id }}">
                    {{ $leaveType->name }}@if($leaveType->max_days) ({{ __('Max') }}: {{ $leaveType->max_days }} {{ __('days') }})@endif
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Start Date') }}" wire:model.live="start_date" required />
            <flux:date-picker label="{{ __('End Date') }}" wire:model.live="end_date" required />
        </div>

        @if($this->totalDays() > 0)
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-3 text-sm text-blue-700 dark:text-blue-300">
                {{ __('Total days') }}: <strong>{{ $this->totalDays() }}</strong>
            </div>
        @endif

        <flux:textarea label="{{ __('Reason') }}" wire:model="reason" rows="4" required />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Submit') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-leave-application')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
