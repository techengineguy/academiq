<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Complaint Details')]
class extends Component {

    public int $id;

    public string $status = 'open';
    public string $assigned_to = '';
    public string $resolution = '';

    public function mount(int $id): void
    {
        $this->id = $id;

        $complaint = $this->complaint;
        $this->status = $complaint->status;
        $this->assigned_to = (string) ($complaint->assigned_to ?? '');
        $this->resolution = (string) ($complaint->resolution ?? '');
    }

    #[Computed]
    public function complaint()
    {
        return Complaint::where('tenant_id', Auth::user()->tenant_id)
            ->with(['submittedBy', 'assignedTo'])
            ->findOrFail($this->id);
    }

    #[Computed]
    public function staff()
    {
        return User::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('role', ['admin', 'staff'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'resolution' => ['nullable', 'string'],
        ]);

        $data = [
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?: null,
            'resolution' => $validated['resolution'] ?: null,
        ];

        if (in_array($validated['status'], ['resolved', 'closed']) && ! $this->complaint->resolved_at) {
            $data['resolved_at'] = now();
        }

        $this->complaint->update($data);

        Flux::toast(variant: 'success', text: __('Complaint updated successfully.'));
        unset($this->complaint);
    }
};
?>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Complaint Details') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $this->complaint->complaint_number }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('complaints.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main complaint details --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card>
                <div class="flex items-start justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->complaint->subject }}</h2>
                    @php
                        $priorityColor = match($this->complaint->priority) {
                            'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'gray', default => 'gray',
                        };
                        $statusColor = match($this->complaint->status) {
                            'open' => 'red', 'in_progress' => 'yellow', 'resolved' => 'green', 'closed' => 'gray', default => 'gray',
                        };
                    @endphp
                    <div class="flex gap-2">
                        <flux:badge :color="$priorityColor">{{ ucfirst($this->complaint->priority) }}</flux:badge>
                        <flux:badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $this->complaint->status)) }}</flux:badge>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 mb-4 pb-4 border-b border-gray-200 dark:border-zinc-700">
                    <div>
                        <p class="text-xs text-gray-500">{{ __('Category') }}</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($this->complaint->category) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ __('Submitted By') }}</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $this->complaint->submittedBy?->first_name }} {{ $this->complaint->submittedBy?->last_name }}
                        </p>
                        <p class="text-xs text-gray-500">{{ ucfirst($this->complaint->submittedBy?->role ?? '') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ __('Submitted On') }}</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $this->complaint->created_at?->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-2">{{ __('Description') }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $this->complaint->description }}</p>
                </div>

                @if($this->complaint->resolution)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-zinc-700">
                        <p class="text-xs text-gray-500 mb-2">{{ __('Resolution') }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $this->complaint->resolution }}</p>
                        @if($this->complaint->resolved_at)
                            <p class="text-xs text-gray-400 mt-1">{{ __('Resolved on') }}: {{ $this->complaint->resolved_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Action panel --}}
        <div class="space-y-6">
            <flux:card>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('Update Complaint') }}</h3>

                <form wire:submit="update" class="space-y-4">
                    <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                        <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
                        <flux:select.option value="in_progress">{{ __('In Progress') }}</flux:select.option>
                        <flux:select.option value="resolved">{{ __('Resolved') }}</flux:select.option>
                        <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
                    </flux:select>

                    <flux:select label="{{ __('Assign To') }}" variant="listbox" wire:model="assigned_to" searchable>
                        <flux:select.option value="">{{ __('Unassigned') }}</flux:select.option>
                        @foreach($this->staff as $member)
                            <flux:select.option value="{{ $member->id }}">
                                {{ $member->first_name }} {{ $member->last_name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:textarea label="{{ __('Resolution Notes') }}" wire:model="resolution" rows="4" placeholder="{{ __('Describe how the complaint was resolved...') }}" />

                    <flux:button type="submit" variant="primary" class="button w-full">{{ __('Update') }}</flux:button>
                </form>
            </flux:card>

            <flux:card>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Details') }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Assigned To') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $this->complaint->assignedTo ? $this->complaint->assignedTo->first_name . ' ' . $this->complaint->assignedTo->last_name : __('Unassigned') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('Last Updated') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $this->complaint->updated_at?->diffForHumans() }}</span>
                    </div>
                    @if($this->complaint->resolved_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __('Resolved At') }}</span>
                            <span class="font-medium text-green-600">{{ $this->complaint->resolved_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>
    </div>
</div>
