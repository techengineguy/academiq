<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Submit Complaint')]
class extends Component {

    public string $subject = '';
    public string $description = '';
    public string $category = 'other';
    public string $priority = 'medium';
    public string $submitted_by = '';
    public string $assigned_to = '';

    public function mount(): void
    {
        $this->submitted_by = (string) Auth::id();
    }

    #[Computed]
    public function users()
    {
        return User::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
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

    public function save(): void
    {
        $validated = $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:academic,hostel,transport,infrastructure,staff,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'submitted_by' => ['required', 'exists:users,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        Complaint::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'submitted_by' => $validated['submitted_by'],
            'complaint_number' => 'CMP-' . strtoupper(Str::random(8)),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'assigned_to' => $validated['assigned_to'] ?: null,
            'status' => 'open',
        ]);

        Flux::toast(variant: 'success', text: __('Complaint submitted successfully.'));

        $this->redirect(route('complaints.index'), navigate: true);
    }
};
?>
<div>
    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Subject') }}" wire:model="subject" placeholder="{{ __('Brief description of the issue') }}" required />

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="{{ __('Category') }}" variant="listbox" wire:model="category" required>
                <flux:select.option value="academic">{{ __('Academic') }}</flux:select.option>
                <flux:select.option value="hostel">{{ __('Hostel') }}</flux:select.option>
                <flux:select.option value="transport">{{ __('Transport') }}</flux:select.option>
                <flux:select.option value="infrastructure">{{ __('Infrastructure') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>

            <flux:select label="{{ __('Priority') }}" variant="listbox" wire:model="priority" required>
                <flux:select.option value="low">{{ __('Low') }}</flux:select.option>
                <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                <flux:select.option value="high">{{ __('High') }}</flux:select.option>
                <flux:select.option value="urgent">{{ __('Urgent') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:select label="{{ __('Submitted By') }}" variant="listbox" wire:model="submitted_by" searchable required>
            <flux:select.option value="">{{ __('Select User') }}</flux:select.option>
            @foreach($this->users as $user)
                <flux:select.option value="{{ $user->id }}">
                    {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="{{ __('Assign To') }}" variant="listbox" wire:model="assigned_to" searchable>
            <flux:select.option value="">{{ __('Unassigned') }}</flux:select.option>
            @foreach($this->staff as $member)
                <flux:select.option value="{{ $member->id }}">
                    {{ $member->first_name }} {{ $member->last_name }} ({{ ucfirst($member->role) }})
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="5" placeholder="{{ __('Provide full details of the complaint...') }}" required />

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary" class="button">{{ __('Submit') }}</flux:button>
            <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('create-complaint')">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
