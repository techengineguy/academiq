<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Concerns\ScopesToParentChildren;
use App\Models\Attendance;
use App\Models\FeeInvoice;
use App\Models\ExamResult;
use App\Models\Assignment;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

new
#[Title('Parent Dashboard')]
#[Layout('layouts.parent')]
class extends Component {
    use ScopesToParentChildren;

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

    #[Computed]
    public function totalChildren(): int
    {
        return $this->parentChildren()->count();
    }

    #[Computed]
    public function pendingFees(): float
    {
        return (float) FeeInvoice::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('student_id', $this->parentChildIds())
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    #[Computed]
    public function activeAssignments(): int
    {
        $classIds = $this->parentChildren()->pluck('class_id')->unique()->all();

        return (int) Assignment::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('class_id', $classIds)
            ->where('due_date', '>=', now())
            ->count();
    }

    #[Computed]
    public function avgAttendance(): float
    {
        $childIds = $this->parentChildIds();
        if (empty($childIds)) {
            return 0;
        }

        $total = Attendance::whereIn('student_id', $childIds)->count();
        $present = Attendance::whereIn('student_id', $childIds)->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    #[Computed]
    public function upcomingEvents()
    {
        return Event::where('tenant_id', Auth::user()->tenant_id)
            ->where('start_date', '>=', now())
            ->where('status', 'upcoming')
            ->orderBy('start_date')
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function recentResults()
    {
        return ExamResult::whereIn('student_id', $this->parentChildIds())
            ->with(['examSchedule.subject', 'examSchedule.exam', 'student.user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }
};
?>
<div>
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Welcome') }}, {{ Auth::user()->first_name }}!</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Stay updated on your children\'s progress.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 w-fit mb-4">
                <flux:icon name="users" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->totalChildren }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('My Children') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 w-fit mb-4">
                <flux:icon name="check-circle" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->avgAttendance }}%</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Avg Attendance') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 w-fit mb-4">
                <flux:icon name="receipt-percent" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ number_format($this->pendingFees, 0) }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Pending Fees') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 w-fit mb-4">
                <flux:icon name="document-text" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->activeAssignments }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Active Assignments') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card>
            <flux:heading size="sm" class="font-semibold mb-4">{{ __('My Children') }}</flux:heading>
            <div class="space-y-3">
                @forelse($this->children as $child)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="($child->user?->first_name ?? '') . ' ' . ($child->user?->last_name ?? '')" />
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $child->user?->first_name }} {{ $child->user?->last_name }}
                                </p>
                                <p class="text-xs text-zinc-500">{{ $child->class?->name }} &middot; {{ $child->admission_number }}</p>
                            </div>
                        </div>
                        <flux:badge :color="$child->status === 'active' ? 'green' : 'gray'" size="sm">
                            {{ ucfirst($child->status) }}
                        </flux:badge>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No children linked to your account yet') }}</p>
                @endforelse
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="sm" class="font-semibold mb-4">{{ __('Recent Results') }}</flux:heading>
            <div class="space-y-3">
                @forelse($this->recentResults as $result)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $result->student?->user?->first_name }} - {{ $result->examSchedule?->subject?->name }}
                            </p>
                            <p class="text-xs text-zinc-500">{{ $result->examSchedule?->exam?->name }}</p>
                        </div>
                        <div class="text-right">
                            @if($result->is_absent)
                                <flux:badge color="gray" size="sm">{{ __('Absent') }}</flux:badge>
                            @else
                                <p class="text-sm font-bold">{{ $result->marks_obtained }}/{{ $result->total_marks }}</p>
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
        </flux:card>
    </div>

    <flux:card>
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="sm" class="font-semibold">{{ __('Upcoming Events') }}</flux:heading>
            <flux:button variant="ghost" size="xs" :href="route('parent.events')" wire:navigate>{{ __('View All') }}</flux:button>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            @forelse($this->upcomingEvents as $event)
                <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                            <flux:icon name="calendar-days" class="size-4" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $event->title }}</p>
                            <p class="text-xs text-zinc-500">{{ $event->start_date?->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <flux:badge color="blue" size="sm">{{ ucfirst($event->type ?? 'event') }}</flux:badge>
                </div>
            @empty
                <p class="text-sm text-zinc-400 text-center py-4 col-span-2">{{ __('No upcoming events') }}</p>
            @endforelse
        </div>
    </flux:card>
</div>
</div>
