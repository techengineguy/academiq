<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Review Leave Application')]
class extends Component {

    public ?LeaveApplication $application = null;

    public string $status = 'pending';
    public string $approval_remarks = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadApplication($id);
        }
    }

    #[On('edit-leave-application')]
    public function loadApplication(int $id): void
    {
        $this->application = LeaveApplication::where('tenant_id', Auth::user()->tenant_id)
            ->with(['user', 'leaveType'])
            ->findOrFail($id);

        $this->status = $this->application->status;
        $this->approval_remarks = (string) ($this->application->approval_remarks ?? '');
    }

    public function approve(): void
    {
        $this->updateStatus('approved');
    }

    public function reject(): void
    {
        $this->updateStatus('rejected');
    }

    public function update(): void
    {
        $this->updateStatus($this->status);
    }

    private function updateStatus(string $status): void
    {
        $this->validate([
            'approval_remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $this->application->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'approval_remarks' => $this->approval_remarks ?: null,
            'approved_at' => now(),
        ]);

        $this->status = $status;

        Flux::toast(
            variant: 'success',
            text: $status === 'approved' ? __('Application approved.') : ($status === 'rejected' ? __('Application rejected.') : __('Application updated.'))
        );

        $this->redirect(route('leave-applications.index'), navigate: true);
    }
};
?>
<div>
    @if($this->application)
        <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-zinc-800 space-y-3">
            <div class="grid gap-2 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Employee') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $this->application->user?->first_name }} {{ $this->application->user?->last_name }}
                    </p>
                    <p class="text-xs text-gray-500">{{ ucfirst($this->application->user?->role ?? '') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Leave Type') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $this->application->leaveType?->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Period') }}</p>
                    <p class="text-gray-900 dark:text-white">
                        {{ $this->application->start_date?->format('M d') }} – {{ $this->application->end_date?->format('M d, Y') }}
                        <span class="text-xs text-gray-500">({{ $this->application->total_days }} {{ __('days') }})</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Current Status') }}</p>
                    @php
                        $color = match($this->application->status) {
                            'approved' => 'green', 'pending' => 'yellow', 'rejected' => 'red', default => 'gray',
                        };
                    @endphp
                    <flux:badge :color="$color">{{ ucfirst($this->application->status) }}</flux:badge>
                </div>
            </div>

            @if($this->application->reason)
                <div>
                    <p class="text-xs text-gray-500 mb-1">{{ __('Reason') }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $this->application->reason }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <flux:textarea
                label="{{ __('Approval Remarks') }}"
                wire:model="approval_remarks"
                rows="3"
                placeholder="{{ __('Optional remarks for the applicant...') }}"
            />

            @if($this->application->status === 'pending')
                <div class="flex gap-3">
                    <flux:button type="button" variant="primary" class="button" wire:click="approve" icon="check">
                        {{ __('Approve') }}
                    </flux:button>
                    <flux:button type="button" variant="danger" wire:click="reject" icon="x-mark">
                        {{ __('Reject') }}
                    </flux:button>
                    <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-leave-application')">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            @else
                <div class="flex gap-3">
                    <flux:select label="{{ __('Change Status') }}" variant="listbox" wire:model="status">
                        <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                        <flux:select.option value="approved">{{ __('Approved') }}</flux:select.option>
                        <flux:select.option value="rejected">{{ __('Rejected') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex gap-3">
                    <flux:button type="button" variant="primary" class="button" wire:click="update">{{ __('Update') }}</flux:button>
                    <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-leave-application')">{{ __('Cancel') }}</flux:button>
                </div>
            @endif
        </div>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">
            {{ __('Loading...') }}
        </div>
    @endif
</div>
