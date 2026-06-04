<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Classes')]
#[Layout('layouts.teacher')]
class extends Component {

    #[Computed]
    public function classSubjects()
    {
        return ClassSubject::where('teacher_id', Auth::id())
            ->with(['class.sections', 'subject'])
            ->get()
            ->groupBy(fn ($cs) => $cs->class?->name ?? 'Unknown');
    }

    #[Computed]
    public function studentsByClass(): array
    {
        $classIds = ClassSubject::where('teacher_id', Auth::id())
            ->pluck('class_id')
            ->unique();

        $counts = [];
        foreach ($classIds as $classId) {
            $counts[$classId] = Student::where('class_id', $classId)
                ->where('status', 'active')
                ->count();
        }

        return $counts;
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Classes') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Classes and subjects assigned to you.') }}</p>
    </div>

    @forelse($this->classSubjects as $className => $subjects)
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $className }}</h2>
                    <p class="text-xs text-gray-500">
                        {{ $this->studentsByClass[$subjects->first()?->class_id] ?? 0 }} {{ __('students') }}
                    </p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($subjects as $cs)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <flux:icon name="book-open" class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $cs->subject?->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $cs->subject?->code }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:badge color="blue" size="sm">{{ $cs->periods_per_week }} {{ __('periods/wk') }}</flux:badge>
                            @if($cs->is_compulsory)
                                <flux:badge color="green" size="sm">{{ __('Compulsory') }}</flux:badge>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    @empty
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Classes Assigned') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Contact admin to assign classes to you.') }}</p>
            </div>
        </flux:card>
    @endforelse
</div>
</div>
