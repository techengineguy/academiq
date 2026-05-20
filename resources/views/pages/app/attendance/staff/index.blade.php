<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Staff Attendance')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterEmployee = '';
    public string $filterDate = '';
    public string $filterStatus = '';
    public string $filterRole = '';

    public ?int $attendanceIdToDelete = null;

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    #[Computed]
    public function attendances()
    {
        $query = TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)
            ->with(['teacher', 'markedBy'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($this->filterDate !== '') {
            $query->whereDate('date', $this->filterDate);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterEmployee !== '') {
            $query->where('teacher_id', $this->filterEmployee);
        }

        if ($this->filterRole !== '') {
            $query->whereHas('teacher', function ($q): void {
                $q->where('role', $this->filterRole);
            });
        }

        return $query->paginate(15);
    }

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
    public function totalPresent(): int
    {
        return (int) TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'present')
            ->whereDate('date', now())
            ->count();
    }

    #[Computed]
    public function totalAbsent(): int
    {
        return (int) TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'absent')
            ->whereDate('date', now())
            ->count();
    }

    #[Computed]
    public function totalLate(): int
    {
        return (int) TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'late')
            ->whereDate('date', now())
            ->count();
    }

    #[Computed]
    public function totalRecords(): int
    {
        return (int) TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)->count();
    }

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterEmployee(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterEmployee = '';
        $this->filterDate = now()->format('Y-m-d');
        $this->filterStatus = '';
        $this->filterRole = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->attendanceIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this attendance record?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->attendanceIdToDelete) {
            return;
        }

        TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->attendanceIdToDelete)
            ->delete();

        $this->attendanceIdToDelete = null;
        unset($this->attendances);

        Flux::toast(variant: 'success', text: __('Attendance record deleted successfully.'));
    }
};
?>
<div class="space-y-6">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Staff Attendance') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track and manage daily attendance for teachers and staff.') }}</p>
        </div>

        <flux:button class="button" href="{{ route('staff-attendance.create') }}" wire:navigate icon="plus">
            {{ __('Mark Attendance') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Records') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalRecords) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Present Today') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($this->totalPresent) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Absent Today') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->totalAbsent) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Late Today') }}</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($this->totalLate) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <flux:select variant="listbox" wire:model.live="filterEmployee" placeholder="{{ __('All Employees') }}">
                <flux:select.option value="">{{ __('All Employees') }}</flux:select.option>
                @foreach($this->employees as $employee)
                    <flux:select.option value="{{ $employee->id }}">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model.live="filterRole" placeholder="{{ __('All Roles') }}">
                <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                <flux:select.option value="teacher">{{ __('Teacher') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
            </flux:select>

            <flux:date-picker wire:model.live="filterDate" />

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                <flux:select.option value="on_leave">{{ __('On Leave') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->attendances->count())
            <flux:table :paginate="$this->attendances">
                <flux:table.columns>
                    <flux:table.column>{{ __('Employee') }}</flux:table.column>
                    <flux:table.column>{{ __('Role') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Check In') }}</flux:table.column>
                    <flux:table.column>{{ __('Check Out') }}</flux:table.column>
                    <flux:table.column>{{ __('Marked By') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->attendances as $attendance)
                    <flux:table.rows>
                        <flux:table.row :key="$attendance->id">
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $attendance->teacher?->first_name }} {{ $attendance->teacher?->last_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $attendance->teacher?->email }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$attendance->teacher?->role === 'teacher' ? 'blue' : 'purple'">
                                    {{ ucfirst($attendance->teacher?->role ?? '-') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $attendance->date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColor = match($attendance->status) {
                                        'present' => 'green',
                                        'absent' => 'red',
                                        'late' => 'yellow',
                                        'half_day' => 'orange',
                                        'on_leave' => 'blue',
                                        default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$statusColor">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->markedBy?->first_name }} {{ $attendance->markedBy?->last_name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button
                                        size="sm"
                                        variant="subtle"
                                        icon="square-pen"
                                        x-on:click="$tsui.open.slide('edit-staff-attendance'), $wire.dispatch('edit-staff-attendance', { id: {{ $attendance->id }} })"
                                    />
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $attendance->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Attendance Records') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No records found for the selected filters.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="edit-staff-attendance" title="{{ __('Edit Attendance') }}" size="lg">
        <livewire:pages::app.attendance.staff.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
