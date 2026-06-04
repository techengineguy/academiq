<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\IdCard;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Generate ID Card')]
class extends Component {

    public string $user_id = '';
    public string $type = 'student';
    public string $issue_date = '';
    public string $expiry_date = '';
    public string $status = 'active';

    public function mount(): void
    {
        $this->issue_date = now()->format('Y-m-d');
        $this->expiry_date = now()->addYear()->format('Y-m-d');
    }

    #[Computed]
    public function users()
    {
        return User::where('is_active', true)
            ->whereIn('role', ['student', 'teacher', 'staff'])
            ->when($this->type !== '', fn ($q) => $q->where('role', $this->type))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function updatedType(): void
    {
        $this->user_id = '';
        unset($this->users);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:student,teacher,staff'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:issue_date'],
            'status' => ['required', 'in:active,expired,lost,damaged'],
        ]);

        IdCard::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'user_id' => $validated['user_id'],
            'card_number' => 'ID-' . strtoupper(Str::random(8)),
            'type' => $validated['type'],
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'] ?: null,
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('ID card generated successfully.'));

        $this->redirect(route('id-cards.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:select label="{{ __('Type') }}" variant="listbox" wire:model.live="type" required>
            <flux:select.option value="student">{{ __('Student') }}</flux:select.option>
            <flux:select.option value="teacher">{{ __('Teacher') }}</flux:select.option>
            <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
        </flux:select>

        <flux:select label="{{ __('Person') }}" variant="listbox" wire:model="user_id" searchable required>
            <flux:select.option value="">{{ __('Select Person') }}</flux:select.option>
            @foreach($this->users as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->first_name }} {{ $user->last_name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker label="{{ __('Issue Date') }}" wire:model="issue_date" required />
            <flux:date-picker label="{{ __('Expiry Date') }}" wire:model="expiry_date" />
        </div>

        <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
            <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
            <flux:select.option value="lost">{{ __('Lost') }}</flux:select.option>
            <flux:select.option value="damaged">{{ __('Damaged') }}</flux:select.option>
        </flux:select>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Generate') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-id-card')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
