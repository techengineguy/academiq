<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Dashboard')]
class extends Component {
    public array $stats = [];
    public array $recentStudents = [];
    public array $recentActivities = [];
    public array $upcomingEvents = [];
    public array $attendanceData = [];
    public array $feeCollection = [];

    public function mount(): void
    {
        // Replace these with real queries from your models
        $this->stats = [
            ['label' => 'Total Students',    'value' => '1,284',  'change' => '+12%',  'trend' => 'up',   'icon' => 'users',            'color' => 'primary'],
            ['label' => 'Teaching Staff',    'value' => '86',     'change' => '+3%',   'trend' => 'up',   'icon' => 'academic-cap',     'color' => 'secondary'],
            ['label' => 'Active Classes',    'value' => '42',     'change' => '0%',    'trend' => 'flat', 'icon' => 'building-library', 'color' => 'amber'],
            ['label' => 'Fee Collection',    'value' => '₦4.2M',  'change' => '+8%',   'trend' => 'up',   'icon' => 'wallet',           'color' => 'emerald'],
            ['label' => 'Pending Invoices',  'value' => '137',    'change' => '-5%',   'trend' => 'down', 'icon' => 'receipt-percent',  'color' => 'rose'],
            ['label' => 'Leave Requests',    'value' => '14',     'change' => '+2',    'trend' => 'up',   'icon' => 'hand-raised',      'color' => 'violet'],
            ['label' => 'Open Complaints',   'value' => '7',      'change' => '-3',    'trend' => 'down', 'icon' => 'exclamation-circle','color' => 'orange'],
            ['label' => 'Hostel Occupancy',  'value' => '93%',    'change' => '+1%',   'trend' => 'up',   'icon' => 'building-office',  'color' => 'sky'],
        ];

        $this->recentStudents = [
            ['name' => 'Adaeze Okafor',   'class' => 'JSS 3A', 'admission' => 'AQ/2024/001', 'status' => 'active',      'avatar' => 'AO'],
            ['name' => 'Emeka Nwosu',     'class' => 'SS 2B',  'admission' => 'AQ/2024/002', 'status' => 'active',      'avatar' => 'EN'],
            ['name' => 'Fatima Bello',    'class' => 'JSS 1C', 'admission' => 'AQ/2024/003', 'status' => 'active',      'avatar' => 'FB'],
            ['name' => 'Chukwudi Eze',    'class' => 'SS 3A',  'admission' => 'AQ/2024/004', 'status' => 'transferred', 'avatar' => 'CE'],
            ['name' => 'Ngozi Adeleke',   'class' => 'JSS 2B', 'admission' => 'AQ/2024/005', 'status' => 'active',      'avatar' => 'NA'],
        ];

        $this->recentActivities = [
            ['action' => 'New admission application submitted',     'subject' => 'Tunde Fashola',    'time' => '5 min ago',  'icon' => 'user-plus',         'color' => 'blue'],
            ['action' => 'Fee payment received',                    'subject' => '₦45,000 — JSS 2A', 'time' => '22 min ago', 'icon' => 'wallet',            'color' => 'green'],
            ['action' => 'Exam results published',                  'subject' => 'Mid-Term 2024',    'time' => '1 hr ago',   'icon' => 'chart-bar',         'color' => 'purple'],
            ['action' => 'Leave request approved',                  'subject' => 'Mr. Akin Salami',  'time' => '2 hrs ago',  'icon' => 'check-circle',      'color' => 'emerald'],
        ];

        $this->upcomingEvents = [
            ['title' => 'Mid-Term Examinations',  'date' => 'May 5–10, 2026',  'type' => 'exam',        'participants' => 1284],
            ['title' => 'Inter-House Sports Day', 'date' => 'May 15, 2026',    'type' => 'event',       'participants' => 420],
            ['title' => 'Parent-Teacher Meeting', 'date' => 'May 20, 2026',    'type' => 'meeting',     'participants' => 180],
            ['title' => 'Science Fair',           'date' => 'May 28, 2026',    'type' => 'event',       'participants' => 300],
        ];

        $this->attendanceData = [
            ['day' => 'Mon', 'students' => 96, 'staff' => 98],
            ['day' => 'Tue', 'students' => 94, 'staff' => 95],
            ['day' => 'Wed', 'students' => 97, 'staff' => 100],
            ['day' => 'Thu', 'students' => 91, 'staff' => 94],
            ['day' => 'Fri', 'students' => 89, 'staff' => 92],
        ];

        $this->feeCollection = [
            ['month' => 'Jan', 'collected' => 3200000, 'target' => 4000000],
            ['month' => 'Feb', 'collected' => 3800000, 'target' => 4000000],
            ['month' => 'Mar', 'collected' => 4100000, 'target' => 4000000],
            ['month' => 'Apr', 'collected' => 4200000, 'target' => 4000000],
        ];
    }
};
?>
<div>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 space-y-6">

    {{-- ── Main Layout: 2 Columns (Left Content + Right Sidebar) ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- ── LEFT COLUMN: Stats, Attendance, Total Students ── --}}
        <div class="lg:col-span-9">
            {{-- ── Stats Grid (4 columns) ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($stats as $stat)
                    @php
                        $colorMap = [
                            'primary'   => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
                            'secondary' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'amber'     => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                            'emerald'   => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'rose'      => 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
                            'violet'    => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
                            'orange'    => 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                            'sky'       => 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
                        ];
                        $trendColor = $stat['trend'] === 'up' ? 'text-emerald-600 dark:text-emerald-400' : ($stat['trend'] === 'down' ? 'text-rose-500' : 'text-zinc-400');
                        $trendIcon  = $stat['trend'] === 'up' ? '↑' : ($stat['trend'] === 'down' ? '↓' : '→');
                    @endphp
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="p-3 rounded-xl {{ $colorMap[$stat['color']] }}">
                                <flux:icon name="{{ $stat['icon'] }}" class="size-6" />
                            </div>
                            <span class="text-xs font-semibold {{ $trendColor }} bg-{{ $stat['trend'] === 'up' ? 'emerald' : ($stat['trend'] === 'down' ? 'rose' : 'zinc') }}-50 px-2 py-1 rounded-lg dark:bg-{{ $stat['trend'] === 'up' ? 'emerald' : ($stat['trend'] === 'down' ? 'rose' : 'zinc') }}-900/30">
                                {{ $trendIcon }} {{ $stat['change'] }}
                            </span>
                        </div>
                        <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">
                            {{ $stat['value'] }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ $stat['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── Attendance & Total Students ── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {{-- Attendance Donut Chart Section --}}
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <flux:heading size="sm" class="font-semibold">Attendance</flux:heading>
                            <flux:text class="text-xs text-zinc-400">Class 8</flux:text>
                        </div>
                        <div class="flex gap-1">
                            <button class="text-xs px-2 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600">Today</button>
                        </div>
                    </div>
                    
                    <!-- Donut Chart -->
                    <div class="flex flex-col items-center justify-center py-8">
                        <div class="relative size-32">
                            <svg viewBox="0 0 100 100" class="size-full transform -rotate-90">
                                <!-- Background circle -->
                                <circle cx="50" cy="50" r="35" fill="none" stroke="#e5e7eb" stroke-width="8" />
                                <!-- Present arc (90%) -->
                                <circle cx="50" cy="50" r="35" fill="none" stroke="#485AE0" stroke-width="8" stroke-dasharray="198.9" stroke-dashoffset="0" stroke-linecap="round" />
                                <!-- Absent arc (10%) -->
                                <circle cx="50" cy="50" r="35" fill="none" stroke="#2DBA8C" stroke-width="8" stroke-dasharray="22.1" stroke-dashoffset="-198.9" stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">90%</div>
                                <div class="text-xs text-zinc-500">Present</div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="grid grid-cols-2 gap-3 mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="size-3 rounded-full bg-indigo-600"></span>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">Present <span class="font-semibold text-zinc-900 dark:text-zinc-100">90%</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="size-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">Absent <span class="font-semibold text-zinc-900 dark:text-zinc-100">10%</span></span>
                        </div>
                    </div>
                </div>

                {{-- Total Students Bar Chart --}}
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <flux:heading size="sm" class="font-semibold">Total Students</flux:heading>
                            <flux:text class="text-xs text-zinc-400">By Class</flux:text>
                        </div>
                        <flux:button variant="ghost" size="xs" :href="route('students.index')" wire:navigate>
                            View All
                        </flux:button>
                    </div>

                    <!-- Simple Bar Chart -->
                    <div class="space-y-4">
                        @php
                            $classData = [
                                ['name' => 'Class 9', 'count' => 85, 'color' => 'bg-rose-400'],
                                ['name' => 'Class 10', 'count' => 72, 'color' => 'bg-sky-400'],
                                ['name' => 'Class 11', 'count' => 95, 'color' => 'bg-emerald-400'],
                                ['name' => 'Class 12', 'count' => 68, 'color' => 'bg-amber-400'],
                            ];
                            $maxCount = 95;
                        @endphp
                        
                        @foreach($classData as $class)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $class['name'] }}</span>
                                    <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-50">{{ $class['count'] }}</span>
                                </div>
                                <div class="h-2 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $class['color'] }} rounded-full transition-all" style="width: {{ ($class['count'] / $maxCount) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT COLUMN: Calendar & Student Activity ── --}}
        <div class="lg:col-span-3">
            {{-- Calendar Widget --}}
            <x-calendar class="p-5 mb-6"/>

            {{-- Student Activity Feed --}}
            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm" class="font-semibold">Student Activity</flux:heading>
                    <flux:button variant="ghost" size="xs" :href="route('activity-logs.index')" wire:navigate>
                        View All
                    </flux:button>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach($recentActivities as $activity)
                        @php
                            $activityIcons = [
                                'Science model making' => ['icon' => 'beaker', 'bg' => 'bg-violet-100 dark:bg-violet-900/30', 'color' => 'text-violet-600 dark:text-violet-400'],
                                'Math Quiz Challenge' => ['icon' => 'calculator', 'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'color' => 'text-amber-600 dark:text-amber-400'],
                                'Group Research Projects' => ['icon' => 'users', 'bg' => 'bg-sky-100 dark:bg-sky-900/30', 'color' => 'text-sky-600 dark:text-sky-400'],
                                'Book Review Corner' => ['icon' => 'book-open', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'color' => 'text-emerald-600 dark:text-emerald-400'],
                            ];
                            $iconData = $activityIcons[$activity['action']] ?? ['icon' => 'star', 'bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'color' => 'text-indigo-600 dark:text-indigo-400'];
                        @endphp

                        <div class="flex gap-3 items-start">
                            <div class="p-2.5 rounded-lg {{ $iconData['bg'] }} {{ $iconData['color'] }} shrink-0">
                                <flux:icon name="{{ $iconData['icon'] }}" class="size-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $activity['action'] }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $activity['subject'] }}</div>
                                <div class="text-xs text-zinc-400 mt-0.5">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    {{-- Fee Collection Chart --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="sm" class="font-semibold">Fees Collection</flux:heading>
                <flux:text class="text-xs text-zinc-400">Monthly Status</flux:text>
            </div>
            <flux:button variant="ghost" size="xs" :href="route('fee-payments.index')" wire:navigate>
                View All
            </flux:button>
        </div>

        <!-- Mini Line Chart with bars -->
        <div class="flex items-end justify-between gap-2 h-32 px-2">
            @php
                $feeData = [
                    ['month' => 'Jan', 'value' => 3200000],
                    ['month' => 'Feb', 'value' => 3800000],
                    ['month' => 'Mar', 'value' => 4100000],
                    ['month' => 'Apr', 'value' => 4200000],
                ];
                $maxFee = 4200000;
            @endphp

            @foreach($feeData as $fee)
                <div class="flex flex-col items-center flex-1">
                    <div class="w-full bg-gradient-to-t from-indigo-500 to-indigo-400 rounded-t-lg transition-all hover:from-indigo-600 hover:to-indigo-500" 
                            style="height: {{ ($fee['value'] / $maxFee) * 100 }}%">
                    </div>
                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 mt-2">{{ $fee['month'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700 grid grid-cols-2 gap-3 text-center">
            <div>
                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-50">₦15.3M</div>
                <div class="text-xs text-zinc-400">Collected</div>
            </div>
            <div>
                <div class="text-lg font-bold text-emerald-500">85%</div>
                <div class="text-xs text-zinc-400">Rate</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Admissions & Quick Actions ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Recent Admissions --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="sm" class="font-semibold">Recent Admissions</flux:heading>
            <flux:button variant="ghost" size="xs" :href="route('students.index')" wire:navigate>
                View All
            </flux:button>
        </div>
        <div class="space-y-3">
            @foreach($recentStudents as $student)
            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <flux:avatar name="{{ $student['name'] }}" :initials="$student['avatar']" size="sm" />
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $student['name'] }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $student['class'] }} · {{ $student['admission'] }}</div>
                    </div>
                </div>
                @if($student['status'] === 'active')
                    <flux:badge color="green" size="sm">Active</flux:badge>
                @else
                    <flux:badge color="yellow" size="sm">{{ ucfirst($student['status']) }}</flux:badge>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Quick Actions --}}
        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700">
            <flux:text class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3">Quick Actions</flux:text>
            <div class="grid grid-cols-2 gap-2">
                <flux:button variant="outline" size="sm" icon="user-plus" :href="route('admission-applications.index')" wire:navigate class="justify-start text-xs">
                    New Admission
                </flux:button>
                <flux:button variant="outline" size="sm" icon="receipt-percent" :href="route('fee-invoices.index')" wire:navigate class="justify-start text-xs">
                    New Invoice
                </flux:button>
                <flux:button variant="outline" size="sm" icon="clipboard-document-check" :href="route('attendance.index')" wire:navigate class="justify-start text-xs">
                    Attendance
                </flux:button>
                <flux:button variant="outline" size="sm" icon="megaphone" :href="route('announcements.index')" wire:navigate class="justify-start text-xs">
                    Announce
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Quick Stats Summary --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
        <flux:heading size="sm" class="font-semibold mb-4">Quick Stats</flux:heading>
        
        <div class="space-y-4">
            <!-- Admission this month -->
            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <flux:icon name="user-plus" class="size-4" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400">New Admissions</div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">This Month</div>
                    </div>
                </div>
                <div class="text-lg font-bold text-blue-600">24</div>
            </div>

            <!-- Pending Fees -->
            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                        <flux:icon name="receipt-percent" class="size-4" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400">Pending Invoices</div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Outstanding</div>
                    </div>
                </div>
                <div class="text-lg font-bold text-rose-600">₦2.7M</div>
            </div>

            <!-- Attendance Today -->
            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <flux:icon name="check-circle" class="size-4" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400">Student Attendance</div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Today</div>
                    </div>
                </div>
                <div class="text-lg font-bold text-emerald-600">96%</div>
            </div>

            <!-- Exams Pending -->
            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <flux:icon name="pencil-square" class="size-4" />
                    </div>
                    <div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400">Pending Exams</div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Next Week</div>
                    </div>
                </div>
                <div class="text-lg font-bold text-amber-600">8</div>
            </div>
        </div>
    </div>
</div>

</div>
