<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\ClassModel;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Attendance;
use App\Models\TeacherAttendance;
use App\Models\LeaveApplication;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

new #[Title('Dashboard')]
class extends Component {

    #[Computed]
    public function totalStudents(): int
    {
        return (int) Student::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->count();
    }

    #[Computed]
    public function totalTeachers(): int
    {
        return (int) Teacher::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->count();
    }

    #[Computed]
    public function totalStaff(): int
    {
        return (int) Staff::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->count();
    }

    #[Computed]
    public function activeClasses(): int
    {
        return (int) ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->count();
    }

    #[Computed]
    public function feeCollected(): float
    {
        return (float) FeePayment::where('tenant_id', Auth::user()->tenant_id)->sum('amount');
    }

    #[Computed]
    public function pendingInvoices(): int
    {
        return (int) FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->count();
    }

    #[Computed]
    public function outstandingBalance(): float
    {
        return (float) FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    #[Computed]
    public function pendingLeaveRequests(): int
    {
        return (int) LeaveApplication::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function openComplaints(): int
    {
        return (int) Complaint::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
    }

    #[Computed]
    public function studentAttendanceToday(): float
    {
        $total = Attendance::where('tenant_id', Auth::user()->tenant_id)->whereDate('date', now())->count();
        $present = Attendance::where('tenant_id', Auth::user()->tenant_id)->whereDate('date', now())->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    #[Computed]
    public function staffAttendanceToday(): float
    {
        $total = TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)->whereDate('date', now())->count();
        $present = TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)->whereDate('date', now())->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    #[Computed]
    public function recentStudents()
    {
        return Student::where('tenant_id', Auth::user()->tenant_id)
            ->with(['user', 'class'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function upcomingEvents()
    {
        return Event::where('tenant_id', Auth::user()->tenant_id)
            ->where('start_date', '>=', now())
            ->where('status', 'published')
            ->orderBy('start_date')
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function recentActivities()
    {
        $tenantUserIds = \App\Models\User::where('tenant_id', Auth::user()->tenant_id)
            ->pluck('id');

        return ActivityLog::whereIn('causer_id', $tenantUserIds)
            ->with('causer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function monthlyFeeCollection(): array
    {
        $data = [];
        for ($i = 3; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $collected = (float) FeePayment::where('tenant_id', Auth::user()->tenant_id)
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
            $data[] = ['month' => $month->format('M'), 'collected' => $collected];
        }

        return $data;
    }
};
?>
<div>
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 space-y-6 py-4">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-9">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                            <flux:icon name="users" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->totalStudents) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Total Students') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <flux:icon name="academic-cap" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->totalTeachers) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Teachers') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                            <flux:icon name="building-library" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->activeClasses) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Active Classes') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <flux:icon name="wallet" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->feeCollected, 0) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Fee Collected') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                            <flux:icon name="receipt-percent" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->pendingInvoices) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Pending Invoices') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                            <flux:icon name="hand-raised" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->pendingLeaveRequests) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Leave Requests') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                            <flux:icon name="exclamation-circle" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->openComplaints) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Open Complaints') }}</div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400">
                            <flux:icon name="briefcase" class="size-6" />
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->totalStaff) }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Staff Members') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <flux:heading size="sm" class="font-semibold">{{ __('Attendance Today') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-400">{{ now()->format('M d, Y') }}</flux:text>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center py-8">
                        <div class="relative size-32">
                            @php $pct = $this->studentAttendanceToday; @endphp
                            <svg viewBox="0 0 100 100" class="size-full transform -rotate-90">
                                <circle cx="50" cy="50" r="35" fill="none" stroke="#e5e7eb" stroke-width="8" class="dark:stroke-zinc-700" />
                                <circle cx="50" cy="50" r="35" fill="none" stroke="#485AE0" stroke-width="8" stroke-dasharray="{{ $pct * 2.199 }}" stroke-dashoffset="0" stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{{ $pct }}%</div>
                                <div class="text-xs text-zinc-500">{{ __('Students') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="size-3 rounded-full bg-indigo-600"></span>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Students') }} <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $pct }}%</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="size-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Staff') }} <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->staffAttendanceToday }}%</span></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <flux:heading size="sm" class="font-semibold">{{ __('Fee Collection') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-400">{{ __('Last 4 Months') }}</flux:text>
                        </div>
                        <flux:button variant="ghost" size="xs" :href="route('fee-payments.index')" wire:navigate>
                            {{ __('View All') }}
                        </flux:button>
                    </div>

                    @php
                        $feeData = $this->monthlyFeeCollection;
                        $maxFee = max(array_column($feeData, 'collected')) ?: 1;
                    @endphp
                    <div class="flex items-end justify-between gap-2 h-32 px-2">
                        @foreach($feeData as $fee)
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-full bg-gradient-to-t from-indigo-500 to-indigo-400 rounded-t-lg" style="height: {{ ($fee['collected'] / $maxFee) * 100 }}%"></div>
                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 mt-2">{{ $fee['month'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700 grid grid-cols-2 gap-3 text-center">
                        <div>
                            <div class="text-lg font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->feeCollected, 0) }}</div>
                            <div class="text-xs text-zinc-400">{{ __('Total Collected') }}</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-rose-500">{{ number_format($this->outstandingBalance, 0) }}</div>
                            <div class="text-xs text-zinc-400">{{ __('Outstanding') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="mb-6">
                <flux:calendar selectable-header size="xs" />
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm" class="font-semibold">{{ __('Recent Activity') }}</flux:heading>
                    <flux:button variant="ghost" size="xs" :href="route('activity-logs.index')" wire:navigate>
                        {{ __('View All') }}
                    </flux:button>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @forelse($this->recentActivities as $activity)
                        <div class="flex gap-3 items-start">
                            <div class="p-2.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 shrink-0">
                                <flux:icon name="clipboard-document-list" class="size-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-50 truncate">{{ $activity->description ?? '-' }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $activity->causer?->first_name }} {{ $activity->causer?->last_name }}</div>
                                <div class="text-xs text-zinc-400 mt-0.5">{{ $activity->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-400 text-center py-4">{{ __('No recent activity') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="sm" class="font-semibold">{{ __('Recent Students') }}</flux:heading>
                <flux:button variant="ghost" size="xs" :href="route('students.index')" wire:navigate>
                    {{ __('View All') }}
                </flux:button>
            </div>
            <div class="space-y-3">
                @forelse($this->recentStudents as $student)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <flux:avatar :name="($student->user?->first_name ?? '') . ' ' . ($student->user?->last_name ?? '')" size="sm" />
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $student->user?->first_name }} {{ $student->user?->last_name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $student->class?->name ?? '-' }} &middot; {{ $student->admission_number ?? '-' }}</div>
                            </div>
                        </div>
                        <flux:badge :color="$student->status === 'active' ? 'green' : 'gray'" size="sm">{{ ucfirst($student->status) }}</flux:badge>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No students yet') }}</p>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                <flux:text class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3">{{ __('Quick Actions') }}</flux:text>
                <div class="grid grid-cols-2 gap-2">
                    <flux:button variant="outline" size="sm" icon="user-plus" :href="route('admission-applications.index')" wire:navigate class="justify-start text-xs">
                        {{ __('New Admission') }}
                    </flux:button>
                    <flux:button variant="outline" size="sm" icon="receipt-percent" :href="route('fee-invoices.index')" wire:navigate class="justify-start text-xs">
                        {{ __('New Invoice') }}
                    </flux:button>
                    <flux:button variant="outline" size="sm" icon="clipboard-document-check" :href="route('attendance.index')" wire:navigate class="justify-start text-xs">
                        {{ __('Attendance') }}
                    </flux:button>
                    <flux:button variant="outline" size="sm" icon="megaphone" :href="route('announcements.index')" wire:navigate class="justify-start text-xs">
                        {{ __('Announce') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="sm" class="font-semibold">{{ __('Upcoming Events') }}</flux:heading>
                <flux:button variant="ghost" size="xs" :href="route('events.index')" wire:navigate>
                    {{ __('View All') }}
                </flux:button>
            </div>
            <div class="space-y-3">
                @forelse($this->upcomingEvents as $event)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                <flux:icon name="calendar-days" class="size-4" />
                            </div>
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $event->title }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $event->start_date?->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <flux:badge color="blue" size="sm">{{ ucfirst($event->type ?? 'event') }}</flux:badge>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No upcoming events') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>
