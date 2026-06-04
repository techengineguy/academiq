<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('My Leave')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;

    // Apply form
    public string $leave_type_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';

    #[Computed]
    public function leaveApplications()
    {
        return LeaveApplication::where('user_id', Auth::id())
            ->with('leaveType')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::where(fn ($q) => $q->where('applicable_to', 'all')->orWhere('applicable_to', 'teacher'))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $query = LeaveApplication::where('user_id', Auth::id());

        return [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
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
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $totalDays = (int) now()->parse($validated['start_date'])->diffInDays(now()->parse($validated['end_date'])) + 1;

        LeaveApplication::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'user_id' => Auth::id(),
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        Flux::toast(variant: 'success', text: __('Leave application submitted successfully.'));

        $this->reset(['leave_type_id', 'start_date', 'end_date', 'reason']);
        unset($this->leaveApplications);

        $this->redirect(route('teacher.leave'), navigate: true);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Leave') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Apply for leave and view your application history.') }}</p>
        </div>
        <flux:button class="button" x-on:click="$tsui.open.slide('apply-leave')" icon="plus">
            {{ __('Apply for Leave') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-4">
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Total') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Approved') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->stats['approved'] }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Pending') }}</p>
            <p class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $this->stats['pending'] }}</p>
        </flux:card>
        <flux:card>
            <p class="text-sm text-gray-500">{{ __('Rejected') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->stats['rejected'] }}</p>
        </flux:card>
    </div>

    <flux:card>
        @if($this->leaveApplications->count())
            <flux:table :paginate="$this->leaveApplications">
                <flux:table.columns>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('From') }}</flux:table.column>
                    <flux:table.column>{{ __('To') }}</flux:table.column>
                    <flux:table.column>{{ __('Days') }}</flux:table.column>
                    <flux:table.column>{{ __('Reason') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Remarks') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->leaveApplications as $leave)
                    <flux:table.rows>
                        <flux:table.row :key="$leave->id">
                            <flux:table.cell>{{ $leave->leaveType?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $leave->start_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $leave->end_date?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $leave->total_days }}</flux:table.cell>
                            <flux:table.cell>{{ Str::limit($leave->reason, 30) }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($leave->status) {
                                        'approved' => 'green', 'pending' => 'yellow', 'rejected' => 'red', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$color">{{ ucfirst($leave->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $leave->approval_remarks ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Leave Applications') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Your leave history will appear here.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="apply-leave" title="{{ __('Apply for Leave') }}" size="lg">
        <form wire:submit="apply" class="space-y-6">
            <flux:select label="{{ __('Leave Type') }}" variant="listbox" wire:model="leave_type_id" required>
                <flux:select.option value="">{{ __('Select Leave Type') }}</flux:select.option>
                @foreach($this->leaveTypes as $leaveType)
                    <flux:select.option value="{{ $leaveType->id }}">
                        {{ $leaveType->name }}@if($leaveType->max_days) ({{ __('Max') }}: {{ $leaveType->max_days }} {{ __('days') }})@endif
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

            <flux:textarea label="{{ __('Reason') }}" wire:model="reason" rows="4" placeholder="{{ __('Explain the reason for your leave...') }}" required />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Submit Application') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('apply-leave')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-slide>
</div>
</div>
