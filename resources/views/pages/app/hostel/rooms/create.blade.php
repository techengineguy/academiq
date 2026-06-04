<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\HostelRoom;
use App\Models\HostelBuilding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Create Room')]
class extends Component {

    public string $hostel_building_id = '';
    public string $room_number = '';
    public string $floor = '';
    public string $capacity = '1';
    public string $room_type = '';
    public string $rent_amount = '';
    public string $facilities = '';
    public string $status = 'available';

    #[Computed]
    public function buildings()
    {
        return HostelBuilding::where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'hostel_building_id' => ['required', 'exists:hostel_buildings,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'room_type' => ['nullable', 'string', 'max:100'],
            'rent_amount' => ['nullable', 'numeric', 'min:0'],
            'facilities' => ['nullable', 'string'],
            'status' => ['required', 'in:available,occupied,maintenance'],
        ]);

        HostelRoom::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'hostel_building_id' => $validated['hostel_building_id'],
            'room_number' => $validated['room_number'],
            'floor' => $validated['floor'] ?: null,
            'capacity' => $validated['capacity'],
            'occupied' => 0,
            'room_type' => $validated['room_type'] ?: null,
            'rent_amount' => $validated['rent_amount'] ?: 0,
            'facilities' => $validated['facilities'] ?: null,
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Room created successfully.'));

        $this->redirect(route('hostel-rooms.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Building') }}" variant="listbox" wire:model="hostel_building_id" required>
            <flux:select.option value="">{{ __('Select Building') }}</flux:select.option>
            @foreach($this->buildings as $building)
                <flux:select.option value="{{ $building->id }}">{{ $building->name }} ({{ $building->code }})</flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Room Number') }}" wire:model="room_number" placeholder="{{ __('e.g., 101, A-205') }}" required />
            <flux:input label="{{ __('Floor') }}" type="number" wire:model="floor" min="0" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Capacity') }}" type="number" wire:model="capacity" min="1" required />
            <flux:input label="{{ __('Room Type') }}" wire:model="room_type" placeholder="{{ __('e.g., Single, Double, Dormitory') }}" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Rent Amount') }}" type="text" inputmode="decimal" wire:model="rent_amount" />
            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="available">{{ __('Available') }}</flux:select.option>
                <flux:select.option value="occupied">{{ __('Occupied') }}</flux:select.option>
                <flux:select.option value="maintenance">{{ __('Maintenance') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:textarea label="{{ __('Facilities') }}" wire:model="facilities" rows="3" placeholder="{{ __('e.g., AC, Attached bathroom, Study desk') }}" />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Create') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-room')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
