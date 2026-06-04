<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new #[Title('Edit Announcement')]
class extends Component {

    public ?Announcement $announcement = null;

    public string $title = '';
    public string $content = '';
    public string $target_audience = 'all';
    public string $publish_date = '';
    public string $expiry_date = '';
    public bool $is_urgent = false;
    public bool $send_notification = true;
    public string $status = 'published';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadAnnouncement($id);
        }
    }

    #[On('edit-announcement')]
    public function loadAnnouncement(int $id): void
    {
        $this->announcement = Announcement::findOrFail($id);

        $this->title = $this->announcement->title;
        $this->content = $this->announcement->content;
        $this->target_audience = $this->announcement->target_audience ?? 'all';
        $this->publish_date = $this->announcement->publish_date?->format('Y-m-d') ?? '';
        $this->expiry_date = $this->announcement->expiry_date?->format('Y-m-d') ?? '';
        $this->is_urgent = $this->announcement->is_urgent;
        $this->send_notification = $this->announcement->send_notification;
        $this->status = $this->announcement->status;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'target_audience' => ['required', 'in:all,students,teachers,staff,parents'],
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'is_urgent' => ['boolean'],
            'send_notification' => ['boolean'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $this->announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'target_audience' => $validated['target_audience'],
            'publish_date' => $validated['publish_date'],
            'expiry_date' => $validated['expiry_date'] ?: null,
            'is_urgent' => $validated['is_urgent'],
            'send_notification' => $validated['send_notification'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Announcement updated successfully.'));

        $this->redirect(route('announcements.index'), navigate: true);
    }
};
?>
<div>
    @if($this->announcement)
        <form wire:submit="update" class="space-y-6">
            <flux:input label="{{ __('Title') }}" wire:model="title" required />

            <flux:textarea label="{{ __('Content') }}" wire:model="content" rows="6" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="{{ __('Target Audience') }}" variant="listbox" wire:model="target_audience" required>
                    <flux:select.option value="all">{{ __('Everyone') }}</flux:select.option>
                    <flux:select.option value="students">{{ __('Students') }}</flux:select.option>
                    <flux:select.option value="teachers">{{ __('Teachers') }}</flux:select.option>
                    <flux:select.option value="staff">{{ __('Staff') }}</flux:select.option>
                    <flux:select.option value="parents">{{ __('Parents') }}</flux:select.option>
                </flux:select>

                <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                    <flux:select.option value="published">{{ __('Published') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:date-picker label="{{ __('Publish Date') }}" wire:model="publish_date" required />
                <flux:date-picker label="{{ __('Expiry Date') }}" wire:model="expiry_date" />
            </div>

            <div class="space-y-3">
                <flux:checkbox label="{{ __('Mark as Urgent') }}" wire:model="is_urgent" />
                <flux:checkbox label="{{ __('Send Notification') }}" wire:model="send_notification" />
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                <flux:button type="button" variant="subtle" x-on:click="$tsui.close.slide('edit-announcement')">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex h-32 items-center justify-center text-sm text-zinc-400">{{ __('Loading...') }}</div>
    @endif
</div>
