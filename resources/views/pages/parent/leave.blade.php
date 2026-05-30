<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Concerns\ScopesToParentChildren;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

new
#[Title('Apply Leave for Child')]
#[Layout('layouts.parent')]
class extends Component {
    use WithPagination, ScopesToParentChildren;

    public string $student_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

    #[Computed]
    public function leaveHistory()
    {
        // Show student attendance records with status excused or absent that this parent applied for
        // We use Attendance with remarks containing "Leave applied by parent" since LeaveApplication is for users not students
        $childIds = $this->parentChildIds();

        return Attendance::whereIn('student_id', $childIds)
            ->whereIn('status', ['excused', 'absent'])
            ->with(['student.user'])
            ->orderByDesc('date')
            ->paginate(10);
    }

    public function totalDays(): int
    {
        if ($this->start_date === '' || $this->end_date === '') {
            return 0;
        }
        try {
            return (int) now()->parse($this->start_date)->diffInDays(now()->parse($this->end_date)) + 1;
        } catch (\Exception) {
            return 0;
        }
    }

    public function apply(): void
    {
        $validated = $this->validate([
            'student_id' => ['required', 'exists:students,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // Verify the student belongs to this parent
        if (! in_array((int) $validated['student_id'], $this->parentChildIds(), true)) {
            Flux::toast(variant: 'danger', text: __('Invalid child selected.'));

            return;
        }

        $student = $this->parentChildren()->firstWhere('id', (int) $validated['student_id']);

        DB::transaction(function () use ($validated, $student): void {
            $start = now()->parse($validated['start_date']);
            $end = now()->parse($validated['end_date']);

            for ($date = $start; $date->lte($end); $date = $date->addDay()) {
                Attendance::updateOrCreate(
                    [
                        'tenant_id' => Auth::user()->tenant_id,
                        'student_id' => $validated['student_id'],
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'class_id' => $student->class_id,
                        'section_id' => $student->section_id,
                        'status' => 'excused',
                        'remarks' => 'Leave applied by parent: ' . $validated['reason'],
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        Flux::toast(variant: 'success', text: __('Leave application submitted. The school has been notified.'));

        $this->reset(['student_id', 'start_date', 'end_date', 'reason']);
        unset($this->leaveHistory);

        $this->redirect(route('parent.leave'), navigate: true);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Apply Leave for Child') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Submit leave applications on behalf of your children.') }}</p>
        </div>
        <flux:button class="button" x-on:click="$tsui.open.slide('apply-leave')" icon="plus">
            {{ __('Apply for Leave') }}
        </flux:button>
    </div>

    <flux:card>
        <flux:heading size="sm" class="font-semibold mb-4">{{ __('Recent Excused / Absent Days') }}</flux:heading>
        @if($this->leaveHistory->count())
            <flux:table :paginate="$this->leaveHistory">
                <flux:table.columns>
                    <flux:table.column>{{ __('Child') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Remarks') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->leaveHistory as $record)
                    <flux:table.rows>
                        <flux:table.row :key="$record->id">
                            <flux:table.cell>{{ $record->student?->user?->first_name }} {{ $record->student?->user?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $record->date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$record->status === 'excused' ? 'blue' : 'red'">
                                    {{ ucfirst($record->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ Str::limit($record->remarks, 50) ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Leave Records') }}</h3>
            </div>
        @endif
    </flux:card>

    <x-slide id="apply-leave" title="{{ __('Apply for Leave') }}" size="lg">
        <form wire:submit="apply" class="space-y-6">
            <flux:select label="{{ __('Child') }}" variant="listbox" wire:model="student_id" required>
                <flux:select.option value="">{{ __('Select Child') }}</flux:select.option>
                @foreach($this->children as $child)
                    <flux:select.option value="{{ $child->id }}">
                        {{ $child->user?->first_name }} {{ $child->user?->last_name }} ({{ $child->class?->name }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Start Date') }}" wire:model.live="start_date" required />
                <flux:date-picker label="{{ __('End Date') }}" wire:model.live="end_date" required />
            </div>

            @if($this->totalDays() > 0)
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-3 text-sm text-blue-700 dark:text-blue-300">
                    {{ __('Total days') }}: <strong>{{ $this->totalDays() }}</strong>
                </div>
            @endif

            <flux:textarea label="{{ __('Reason') }}" wire:model="reason" rows="4" placeholder="{{ __('Explain the reason for your child\'s leave...') }}" required />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Submit') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('apply-leave')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-slide>
</div>
</div>
