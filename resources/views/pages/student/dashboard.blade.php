<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Attendance;
use App\Models\FeeInvoice;
use App\Models\ExamResult;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;

new
#[Title('Student Dashboard')]
#[Layout('layouts.student')]
class extends Component {

    #[Computed]
    public function student()
    {
        return Auth::user()->student;
    }

    #[Computed]
    public function attendanceRate(): float
    {
        $student = $this->student;
        if (! $student) {
            return 0;
        }

        $total = Attendance::where('student_id', $student->id)->count();
        $present = Attendance::where('student_id', $student->id)->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    #[Computed]
    public function pendingFees(): float
    {
        $student = $this->student;
        if (! $student) {
            return 0;
        }

        return (float) FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    #[Computed]
    public function totalExams(): int
    {
        $student = $this->student;
        if (! $student) {
            return 0;
        }

        return (int) ExamResult::where('student_id', $student->id)->count();
    }

    #[Computed]
    public function pendingAssignments(): int
    {
        $student = $this->student;
        if (! $student) {
            return 0;
        }

        return (int) Assignment::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $student->class_id)
            ->where('due_date', '>=', now())
            ->count();
    }

    #[Computed]
    public function recentResults()
    {
        $student = $this->student;
        if (! $student) {
            return collect();
        }

        return ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentAttendance()
    {
        $student = $this->student;
        if (! $student) {
            return collect();
        }

        return Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(7)
            ->get();
    }
};
?>
<div>
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Welcome') }}, {{ Auth::user()->first_name }}!</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $this->student?->class?->name ?? '-' }} &middot; {{ __('Roll') }}: {{ $this->student?->roll_number ?? '-' }}
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 w-fit mb-4">
                <flux:icon name="check-circle" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->attendanceRate }}%</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Attendance Rate') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 w-fit mb-4">
                <flux:icon name="receipt-percent" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->pendingFees, 0) }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Pending Fees') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 w-fit mb-4">
                <flux:icon name="chart-bar" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->totalExams }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Exams Taken') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 w-fit mb-4">
                <flux:icon name="document-text" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->pendingAssignments }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Active Assignments') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
            <flux:heading size="sm" class="font-semibold mb-4">{{ __('Recent Results') }}</flux:heading>
            <div class="space-y-3">
                @forelse($this->recentResults as $result)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $result->examSchedule?->subject?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $result->examSchedule?->exam?->name }}</p>
                        </div>
                        <div class="text-right">
                            @if($result->is_absent)
                                <flux:badge color="gray" size="sm">{{ __('Absent') }}</flux:badge>
                            @else
                                <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $result->marks_obtained }}/{{ $result->total_marks }}</p>
                                @if($result->grade)
                                    <flux:badge color="blue" size="sm">{{ $result->grade }}</flux:badge>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No results yet') }}</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
            <flux:heading size="sm" class="font-semibold mb-4">{{ __('Recent Attendance') }}</flux:heading>
            <div class="space-y-3">
                @forelse($this->recentAttendance as $attendance)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $attendance->date?->format('M d, Y') }}</p>
                        @php
                            $color = match($attendance->status) {
                                'present' => 'green',
                                'absent' => 'red',
                                'late' => 'yellow',
                                'half_day' => 'orange',
                                'excused' => 'blue',
                                default => 'gray',
                            };
                        @endphp
                        <flux:badge :color="$color" size="sm">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</flux:badge>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No attendance records') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>
