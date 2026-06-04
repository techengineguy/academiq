<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

new
#[Title('My Complaints')]
#[Layout('layouts.parent')]
class extends Component {
    use WithPagination;

    public string $subject = '';
    public string $description = '';
    public string $category = 'other';
    public string $priority = 'medium';

    #[Computed]
    public function complaints()
    {
        return Complaint::where('submitted_by', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:academic,hostel,transport,infrastructure,staff,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        Complaint::create([
            'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
            'uuid' => Str::uuid(),
            'institution_id' => Auth::user()->institution_id,
            'submitted_by' => Auth::id(),
            'complaint_number' => 'CMP-' . strtoupper(Str::random(8)),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        Flux::toast(variant: 'success', text: __('Complaint submitted successfully.'));

        $this->reset(['subject', 'description', 'category', 'priority']);
        $this->category = 'other';
        $this->priority = 'medium';
        unset($this->complaints);

        $this->redirect(route('parent.complaints'), navigate: true);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Complaints') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Submit and track your complaints.') }}</p>
        </div>
        <flux:button class="button" x-on:click="$tsui.open.slide('submit-complaint')" icon="plus">
            {{ __('Submit Complaint') }}
        </flux:button>
    </div>

    <flux:card>
        @if($this->complaints->count())
            <flux:table :paginate="$this->complaints">
                <flux:table.columns>
                    <flux:table.column>{{ __('Complaint #') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Category') }}</flux:table.column>
                    <flux:table.column>{{ __('Priority') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->complaints as $complaint)
                    <flux:table.rows>
                        <flux:table.row :key="$complaint->id">
                            <flux:table.cell>
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">{{ $complaint->complaint_number }}</code>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ Str::limit($complaint->subject, 40) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue">{{ ucfirst($complaint->category) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $priorityColor = match($complaint->priority) {
                                        'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'gray', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$priorityColor">{{ ucfirst($complaint->priority) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColor = match($complaint->status) {
                                        'open' => 'red', 'in_progress' => 'yellow', 'resolved' => 'green', 'closed' => 'gray', default => 'gray',
                                    };
                                @endphp
                                <flux:badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $complaint->status)) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $complaint->created_at?->format('M d, Y') }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Complaints') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('You have not submitted any complaints yet.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="submit-complaint" title="{{ __('Submit Complaint') }}" size="lg">
        <form wire:submit="submit" class="space-y-6">
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

            <flux:textarea label="{{ __('Description') }}" wire:model="description" rows="5" placeholder="{{ __('Provide full details of your complaint...') }}" required />

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Submit') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('submit-complaint')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-slide>
</div>
</div>

