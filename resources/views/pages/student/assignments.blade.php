<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Assignments')]
#[Layout('layouts.student')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function assignments()
    {
        $student = Auth::user()->student;
        if (! $student) {
            return Assignment::where('id', 0)->paginate(10);
        }

        return Assignment::where('tenant_id', Auth::user()->tenant_id)
            ->where('class_id', $student->class_id)
            ->with(['subject', 'teacher'])
            ->orderByDesc('due_date')
            ->paginate(10);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Assignments') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View assignments for your class.') }}</p>
    </div>

    <flux:card>
        @if($this->assignments->count())
            <flux:table :paginate="$this->assignments">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                    <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->assignments as $assignment)
                    <flux:table.rows>
                        <flux:table.row :key="$assignment->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $assignment->subject?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->teacher?->first_name }} {{ $assignment->teacher?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->due_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($assignment->due_date && $assignment->due_date->isPast())
                                    <flux:badge color="red">{{ __('Overdue') }}</flux:badge>
                                @else
                                    <flux:badge color="green">{{ __('Active') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Assignments') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
