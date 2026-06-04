<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

new
#[Title('Announcements')]
#[Layout('layouts.student')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function announcements()
    {
        return Announcement::where('status', 'published')
            ->whereIn('target_audience', ['all', 'students'])
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('publish_date')->orWhere('publish_date', '<=', now());
            })
            ->with('createdBy')
            ->orderByDesc('publish_date')
            ->paginate(10);
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Announcements') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Latest announcements from your school.') }}</p>
    </div>

    @if($this->announcements->count())
        <div class="space-y-4">
            @foreach($this->announcements as $announcement)
                <flux:card>
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $announcement->title }}</h2>
                                @if($announcement->is_urgent)
                                    <flux:badge color="red">{{ __('Urgent') }}</flux:badge>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ __('By') }} {{ $announcement->createdBy?->first_name }} {{ $announcement->createdBy?->last_name }}
                                &middot; {{ $announcement->publish_date?->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $announcement->content }}</p>
                </flux:card>
            @endforeach
        </div>
        <div>{{ $this->announcements->links() }}</div>
    @else
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="megaphone" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Announcements') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Check back later for updates.') }}</p>
            </div>
        </flux:card>
    @endif
</div>
</div>
