<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Student Attendance')]
class extends Component {

    public ?Attendance $attendance = null;

    public string $status = 'present';
    public string $check_in_time = '';
    public string $check_out_time = '';
    public string $remarks = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadAttendance($id);
        }
    }

    #[On('edit-student-attendance')]
    public function loadAttendance(int $id): void
    {
        $this->attendance = Attendance::where('tenant_id', Auth::user()->tenant_id)
            ->with(['student.user', 'class', 'section'])
            ->findOrFail($id);

        $this->status = $this->attendance->status;
        $this->check_in_time = $this->attendance->check_in_time?->format('H:i') ?? '';
        $this->check_out_time = $this->attendance->check_out_time?->format('H:i') ?? '';
        $this->remarks = (string) ($this->attendance->remarks ?? '');
    }

    public function update(): void
    {
        $this->validate([
            'status' => ['required', 'in:present,absent,late,half_day,excused'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $this->attendance->update([
            'status' => $this->status,
            'check_in_time' => $this->check_in_time !== '' ? $this->check_in_time : null,
            'check_out_time' => $this->check_out_time !== '' ? $this->check_out_time : null,
            'remarks' => $this->remarks !== '' ? $this->remarks : null,
            'marked_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: __('Attendance updated successfully.'));

        $this->redirect(route('attendance.index'), navigate: true);
    }
};
?>
<div>
    @if($this->attendance)
        <form wire:submit="update" class="space-y-5">
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-zinc-800">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $this->attendance->student?->user?->first_name }} {{ $this->attendance->student?->user?->last_name }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $this->attendance->class?->name }} &middot; {{ $this->attendance->section?->name }} &middot;
                    {{ $this->attendance->date?->format('M d, Y') }}
                </p>
            </div>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                <flux:select.option value="excused">{{ __('Excused') }}</flux:select.option>
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:time-picker label="{{ __('Check In Time') }}" wire:model="check_in_time" />
                <flux:time-picker label="{{ __('Check Out Time') }}" wire:model="check_out_time" />
            </div>

            <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-student-attendance')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
