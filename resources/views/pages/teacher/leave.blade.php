<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Leave')]
#[Layout('layouts.teacher')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function leaveApplications()
    {
        return LeaveApplication::where('tenant_id', Auth::user()->tenant_id)
            ->where('user_id', Auth::id())
            ->with('leaveType')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        $query = LeaveApplication::where('tenant_id', Auth::user()->tenant_id)->where('user_id', Auth::id());

        return [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Leave') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View your leave applications and history.') }}</p>
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
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Leave Applications') }}</h3>
            </div>
        @endif
    </flux:card>
</div>
</div>
