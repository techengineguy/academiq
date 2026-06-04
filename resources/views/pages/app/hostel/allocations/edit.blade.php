<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\HostelAllocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

new #[Title('Edit Allocation')]
class extends Component {

    public ?HostelAllocation $allocation = null;

    public string $allocated_date = '';
    public string $vacated_date = '';
    public string $bed_number = '';
    public string $status = 'active';
    public string $remarks = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadAllocation($id);
        }
    }

    #[On('edit-allocation')]
    public function loadAllocation(int $id): void
    {
        $this->allocation = HostelAllocation::with(['student.user', 'hostelRoom.hostelBuilding'])
            ->findOrFail($id);

        $this->allocated_date = $this->allocation->allocated_date?->format('Y-m-d') ?? '';
        $this->vacated_date = $this->allocation->vacated_date?->format('Y-m-d') ?? '';
        $this->bed_number = (string) ($this->allocation->bed_number ?? '');
        $this->status = $this->allocation->status;
        $this->remarks = (string) ($this->allocation->remarks ?? '');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'allocated_date' => ['required', 'date'],
            'vacated_date' => ['nullable', 'date', 'after_or_equal:allocated_date'],
            'bed_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,vacated'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated): void {
            $wasActive = $this->allocation->status === 'active';
            $isActive = $validated['status'] === 'active';

            $this->allocation->update([
                'allocated_date' => $validated['allocated_date'],
                'vacated_date' => $validated['vacated_date'] ?: null,
                'bed_number' => $validated['bed_number'] ?: null,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?: null,
            ]);

            // Update room occupancy if status changed
            if ($wasActive && ! $isActive && $this->allocation->hostelRoom) {
                $this->allocation->hostelRoom->decrement('occupied');
                if ($this->allocation->hostelRoom->status === 'occupied') {
                    $this->allocation->hostelRoom->update(['status' => 'available']);
                }
            } elseif (! $wasActive && $isActive && $this->allocation->hostelRoom) {
                $this->allocation->hostelRoom->increment('occupied');
            }
        });

        Flux::toast(variant: 'success', text: __('Allocation updated successfully.'));

        $this->redirect(route('hostel-allocations.index'), navigate: true);
    }
};
?>
<div>
    @if($this->allocation)
        <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-zinc-800">
            <div class="grid gap-2 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Student') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $this->allocation->student?->user?->first_name }} {{ $this->allocation->student?->user?->last_name }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Room') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $this->allocation->hostelRoom?->hostelBuilding?->name }} - {{ $this->allocation->hostelRoom?->room_number }}
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Allocated Date') }}" wire:model="allocated_date" required />
                <flux:date-picker label="{{ __('Vacated Date') }}" wire:model="vacated_date" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Bed Number') }}" wire:model="bed_number" />
                <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="vacated">{{ __('Vacated') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:textarea label="{{ __('Remarks') }}" wire:model="remarks" rows="3" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-allocation')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">{{ __('Loading...') }}</div>
    @endif
</div>
