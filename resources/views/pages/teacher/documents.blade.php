<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\IdCard;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Documents')]
#[Layout('layouts.teacher')]
class extends Component {

    #[Computed]
    public function idCards()
    {
        return IdCard::where('user_id', Auth::id())
            ->where('type', 'teacher')
            ->orderByDesc('issue_date')
            ->get();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Documents') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Download your ID card.') }}</p>
    </div>

    <flux:card>
        <flux:heading size="sm" class="font-semibold mb-4">{{ __('ID Cards') }}</flux:heading>

        @if($this->idCards->count())
            <div class="space-y-3">
                @foreach($this->idCards as $card)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                <flux:icon name="identification" class="size-6" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ __('Teacher ID Card') }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ __('Card No') }}: {{ $card->card_number }}
                                    &middot; {{ __('Issued') }}: {{ $card->issue_date?->format('M d, Y') }}
                                    &middot; {{ __('Valid till') }}: {{ $card->expiry_date?->format('M Y') ?? __('N/A') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $statusColor = match($card->status) {
                                    'active' => 'green', 'expired' => 'red', 'lost' => 'yellow', 'damaged' => 'orange', default => 'gray',
                                };
                            @endphp
                            <flux:badge :color="$statusColor">{{ ucfirst($card->status) }}</flux:badge>
                            @if($card->status === 'active')
                                <flux:button
                                    variant="primary"
                                    class="button"
                                    icon="arrow-down-tray"
                                    :href="route('id-cards.download', $card->id)"
                                    target="_blank"
                                >
                                    {{ __('Download') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="identification" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No ID Card') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Your ID card has not been issued yet. Contact the admin.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
</div>
