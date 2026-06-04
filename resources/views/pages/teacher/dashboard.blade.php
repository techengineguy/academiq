<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\ClassSubject;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\LessonPlan;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;

new
#[Title('Teacher Dashboard')]
#[Layout('layouts.teacher')]
class extends Component {

    #[Computed]
    public function myClassesCount(): int
    {
        return (int) ClassSubject::where('teacher_id', Auth::id())
            ->distinct('class_id')
            ->count('class_id');
    }

    #[Computed]
    public function mySubjectsCount(): int
    {
        return (int) ClassSubject::where('teacher_id', Auth::id())
            ->count();
    }

    #[Computed]
    public function activeAssignments(): int
    {
        return (int) Assignment::where('teacher_id', Auth::id())
            ->where('due_date', '>=', now())
            ->count();
    }

    #[Computed]
    public function pendingLeave(): int
    {
        return (int) LeaveApplication::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function recentAssignments()
    {
        return Assignment::where('teacher_id', Auth::id())
            ->with(['class', 'subject'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function myClasses()
    {
        return ClassSubject::where('teacher_id', Auth::id())
            ->with(['class', 'subject'])
            ->get();
    }
};
?>
<div>
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Welcome') }}, {{ Auth::user()->first_name }}!</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ Auth::user()->teacher?->designation ?? __('Teacher') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 w-fit mb-4">
                <flux:icon name="building-library" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->myClassesCount }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('My Classes') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 w-fit mb-4">
                <flux:icon name="book-open" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->mySubjectsCount }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Subjects Teaching') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 w-fit mb-4">
                <flux:icon name="document-text" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->activeAssignments }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Active Assignments') }}</div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-700">
            <div class="p-3 rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400 w-fit mb-4">
                <flux:icon name="hand-raised" class="size-6" />
            </div>
            <div class="text-3xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->pendingLeave }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Pending Leave') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
            <flux:heading size="sm" class="font-semibold mb-4">{{ __('My Classes & Subjects') }}</flux:heading>
            <div class="space-y-3">
                @forelse($this->myClasses as $cs)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $cs->class?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $cs->subject?->name }}</p>
                        </div>
                        <flux:badge color="blue" size="sm">{{ $cs->periods_per_week }} {{ __('periods/wk') }}</flux:badge>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No classes assigned') }}</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5">
            <flux:heading size="sm" class="font-semibold mb-4">{{ __('Recent Assignments') }}</flux:heading>
            <div class="space-y-3">
                @forelse($this->recentAssignments as $assignment)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $assignment->title }}</p>
                            <p class="text-xs text-zinc-500">{{ $assignment->class?->name }} &middot; {{ $assignment->subject?->name }}</p>
                        </div>
                        @if($assignment->due_date?->isPast())
                            <flux:badge color="red" size="sm">{{ __('Overdue') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ $assignment->due_date?->format('M d') }}</flux:badge>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">{{ __('No assignments yet') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>
