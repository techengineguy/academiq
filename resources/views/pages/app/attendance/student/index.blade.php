<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new #[Title('Student Attendance')]
class extends Component {
    use WithPagination;
    use Interactions;

    public string $filterClassSection = '';
    public string $filterDate = '';
    public string $filterStatus = '';

    public ?int $attendanceIdToDelete = null;

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    #[Computed]
    public function classSections()
    {
        return Section::with('class')
            ->whereHas('class')
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function attendances()
    {
        $query = Attendance::with(['student.user', 'class', 'section', 'markedBy'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        // Students can only see their own attendance
        if (Auth::user()->role === 'student') {
            $query->where('student_id', Auth::user()->student?->id);
        }

        if ($this->filterClassSection !== '') {
            [$classId, $sectionId] = explode('-', $this->filterClassSection);
            $query->where('class_id', $classId)->where('section_id', $sectionId);
        }

        if ($this->filterDate !== '') {
            $query->whereDate('date', $this->filterDate);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function totalPresent(): int
    {
        return (int) Attendance::where('status', 'present')
            ->whereDate('date', now())
            ->count();
    }

    #[Computed]
    public function totalAbsent(): int
    {
        return (int) Attendance::where('status', 'absent')
            ->whereDate('date', now())
            ->count();
    }

    #[Computed]
    public function totalLate(): int
    {
        return (int) Attendance::where('status', 'late')
            ->whereDate('date', now())
            ->count();
    }

    #[Computed]
    public function totalRecords(): int
    {
        return (int) Attendance::count();
    }

    public function updatedFilterClassSection(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterClassSection = '';
        $this->filterDate = now()->format('Y-m-d');
        $this->filterStatus = '';
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

        Attendance::findOrFail($this->attendanceIdToDelete)
            ->delete();

        $this->attendanceIdToDelete = null;
        unset($this->attendances);

        Flux::toast(variant: 'success', text: __('Attendance record deleted successfully.'));
    }
};
?>
<div class="space-y-6 py-4">
    <x-dialog />

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Student Attendance') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track and manage daily student attendance records.') }}</p>
        </div>

        @hasPermission('mark-attendance')
        <flux:button class="button" href="{{ route('attendance.create') }}" wire:navigate icon="plus">
            {{ __('Mark Attendance') }}
        </flux:button>
        @endhasPermission
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Records') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalRecords) }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __("Present Today") }}</p>
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
        <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:select variant="listbox" wire:model.live="filterClassSection" placeholder="{{ __('All Classes') }}">
                <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                @foreach($this->classSections as $section)
                    <flux:select.option value="{{ $section->class_id }}-{{ $section->id }}">
                        {{ $section->class?->name }}-{{ $section->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:date-picker wire:model.live="filterDate" />

            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                <flux:select.option value="excused">{{ __('Excused') }}</flux:select.option>
            </flux:select>

            <flux:button variant="subtle" wire:click="clearFilters" icon="x-mark" class="w-fit">
                {{ __('Clear') }}
            </flux:button>
        </div>

        @if($this->attendances->count())
            <flux:table :paginate="$this->attendances">
                <flux:table.columns>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Section') }}</flux:table.column>
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
                                        {{ $attendance->student?->user?->first_name }} {{ $attendance->student?->user?->last_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $attendance->student?->admission_number ?? '-' }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $attendance->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->section?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColor = match($attendance->status) {
                                        'present' => 'green',
                                        'absent' => 'red',
                                        'late' => 'yellow',
                                        'half_day' => 'orange',
                                        'excused' => 'blue',
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
                                        x-on:click="$tsui.open.slide('edit-student-attendance'), $wire.dispatch('edit-student-attendance', { id: {{ $attendance->id }} })"
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

    <x-slide id="edit-student-attendance" title="{{ __('Edit Attendance') }}" size="lg">
        <livewire:pages::app.attendance.student.edit :id="$slideData['id'] ?? null" />
    </x-slide>
</div>
