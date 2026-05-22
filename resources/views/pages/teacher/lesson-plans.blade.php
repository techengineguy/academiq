<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\LessonPlan;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Lesson Plans')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function lessonPlans()
    {
        return LessonPlan::where('tenant_id', Auth::user()->tenant_id)
            ->where('teacher_id', Auth::id())
            ->with(['class', 'subject'])
            ->orderByDesc('lesson_date')
            ->paginate(10);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Lesson Plans') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Your lesson plans and teaching notes.') }}</p>
    </div>

    <flux:card>
        @if($this->lessonPlans->count())
            <flux:table :paginate="$this->lessonPlans">
                <flux:table.columns>
                    <flux:table.column>{{ __('Topic') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Method') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->lessonPlans as $plan)
                    <flux:table.rows>
                        <flux:table.row :key="$plan->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $plan->topic }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $plan->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $plan->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $plan->lesson_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $plan->teaching_method ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Lesson Plans') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
