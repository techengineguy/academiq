<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\HostelBuilding;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Flux\Flux;

new #[Title('Edit Building')]
class extends Component {

    public ?HostelBuilding $building = null;

    public string $name = '';
    public string $code = '';
    public string $type = 'boys';
    public string $address = '';
    public string $total_floors = '';
    public string $warden_id = '';
    public string $facilities = '';
    public string $status = 'active';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadBuilding($id);
        }
    }

    #[On('edit-building')]
    public function loadBuilding(int $id): void
    {
        $this->building = HostelBuilding::findOrFail($id);

        $this->name = $this->building->name;
        $this->code = $this->building->code;
        $this->type = $this->building->type;
        $this->address = (string) ($this->building->address ?? '');
        $this->total_floors = (string) ($this->building->total_floors ?? '');
        $this->warden_id = (string) ($this->building->warden_id ?? '');
        $this->facilities = (string) ($this->building->facilities ?? '');
        $this->status = $this->building->status;
    }

    #[Computed]
    public function wardens()
    {
        return User::whereIn('role', ['teacher', 'staff'])
            ->orderBy('first_name')
            ->get();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('hostel_buildings', 'code')->ignore($this->building?->id)],
            'type' => ['required', 'in:boys,girls,mixed'],
            'address' => ['nullable', 'string'],
            'total_floors' => ['nullable', 'integer', 'min:1'],
            'warden_id' => ['nullable', 'exists:users,id'],
            'facilities' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->building->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'address' => $validated['address'] ?: null,
            'total_floors' => $validated['total_floors'] ?: null,
            'warden_id' => $validated['warden_id'] ?: null,
            'facilities' => $validated['facilities'] ?: null,
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Building updated successfully.'));

        $this->redirect(route('hostel-buildings.index'), navigate: true);
    }
};
?>
<div>
    @if($this->building)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Name') }}" wire:model="name" required />
                <flux:input label="{{ __('Code') }}" wire:model="code" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Type') }}" variant="listbox" wire:model="type" required>
                    <flux:select.option value="boys">{{ __('Boys') }}</flux:select.option>
                    <flux:select.option value="girls">{{ __('Girls') }}</flux:select.option>
                    <flux:select.option value="mixed">{{ __('Mixed') }}</flux:select.option>
                </flux:select>
                <flux:input label="{{ __('Total Floors') }}" type="number" wire:model="total_floors" min="1" />
            </div>

            <flux:select label="{{ __('Warden') }}" variant="listbox" wire:model="warden_id">
                <flux:select.option value="">{{ __('No Warden Assigned') }}</flux:select.option>
                @foreach($this->wardens as $warden)
                    <flux:select.option value="{{ $warden->id }}">{{ $warden->first_name }} {{ $warden->last_name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>

            <flux:textarea label="{{ __('Address') }}" wire:model="address" rows="2" />
            <flux:textarea label="{{ __('Facilities') }}" wire:model="facilities" rows="3" />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-building')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">{{ __('Loading...') }}</div>
    @endif
</div>
