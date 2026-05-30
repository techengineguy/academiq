<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Concerns\ScopesToParentChildren;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

new
#[Title('Children Attendance')]
#[Layout('layouts.parent')]
class extends Component {
    use WithPagination, ScopesToParentChildren;

    public string $filterChild = '';
    public string $filterStatus = '';

    #[Computed]
    public function children()
    {
        return $this->parentChildren();
    }

    #[Computed]
    public function attendances()
    {
        $query = Attendance::whereIn('student_id', $this->parentChildIds())
            ->with(['student.user', 'class', 'section'])
            ->orderByDesc('date');

        if ($this->filterChild !== '') {
            $query->where('student_id', $this->filterChild);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(15);
    }

    public function updatedFilterChild(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Children Attendance') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Track attendance for all your children.') }}</p>
    </div>

    <flux:card>
        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <flux:select variant="listbox" wire:model.live="filterChild" placeholder="{{ __('All Children') }}">
                <flux:select.option value="">{{ __('All Children') }}</flux:select.option>
                @foreach($this->children as $child)
                    <flux:select.option value="{{ $child->id }}">
                        {{ $child->user?->first_name }} {{ $child->user?->last_name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

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
                    <flux:table.column>{{ __('Child') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Check In') }}</flux:table.column>
                    <flux:table.column>{{ __('Remarks') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->attendances as $attendance)
                    <flux:table.rows>
                        <flux:table.row :key="$attendance->id">
                            <flux:table.cell>{{ $attendance->student?->user?->first_name }} {{ $attendance->student?->user?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $attendance->class?->name ?? '-' }}</flux:table.cell>
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
