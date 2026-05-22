<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Attendance')]
#[Layout('layouts.student')]
class extends Component {
    use WithPagination;

    public string $filterStatus = '';

    #[Computed]
    public function attendances()
    {
        $student = Auth::user()->student;
        if (! $student) {
            return collect();
        }

        $query = Attendance::where('student_id', $student->id)
            ->orderByDesc('date');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $student = Auth::user()->student;
        if (! $student) {
            return ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'rate' => 0];
        }

        $total = Attendance::where('student_id', $student->id)->count();
        $present = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $absent = Attendance::where('student_id', $student->id)->where('status', 'absent')->count();
        $late = Attendance::where('student_id', $student->id)->where('status', 'late')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Attendance') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View your attendance history.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total Days') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Present') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->stats['present'] }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Absent') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->stats['absent'] }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Attendance Rate') }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->stats['rate'] }}%</p>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-4">
            <flux:select variant="listbox" wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                <flux:select.option value="excused">{{ __('Excused') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->attendances->count())
            <flux:table :paginate="$this->attendances">
                <flux:table.columns>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Check In') }}</flux:table.column>
                    <flux:table.column>{{ __('Check Out') }}</flux:table.column>
                    <flux:table.column>{{ __('Remarks') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->attendances as $attendance)
                    <flux:table.rows>
                        <flux:table.row :key="$attendance->id">
                            <flux:table.cell>{{ $attendance->date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($attendance->status) {
                                        'present' => 'green', 'absent' => 'red', 'late' => 'yellow',
                                        'half_day' => 'orange', 'excused' => 'blue', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $attendance->check_in_time?->format('H:i') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->check_out_time?->format('H:i') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->remarks ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Records') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
