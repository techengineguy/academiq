<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Certificate;
use App\Models\IdCard;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Documents')]
#[Layout('layouts.student')]
class extends Component {

    #[Computed]
    public function certificates()
    {
        $student = Auth::user()->student;
        if (! $student) {
            return collect();
        }

        return Certificate::where('student_id', $student->id)
            ->orderByDesc('issue_date')
            ->get();
    }

    #[Computed]
    public function idCard()
    {
        return IdCard::where('user_id', Auth::id())
            ->where('type', 'student')
            ->where('status', 'active')
            ->latest('issue_date')
            ->first();
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Documents') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Download your certificates and ID card.') }}</p>
    </div>

    {{-- ID Card --}}
    <flux:card>
        <flux:heading size="sm" class="font-semibold mb-4">{{ __('ID Card') }}</flux:heading>

        @if($this->idCard)
            <div class="flex items-center justify-between p-4 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        <flux:icon name="identification" class="size-6" />
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ __('Student ID Card') }}</p>
                        <p class="text-xs text-gray-500">
                            {{ __('Card No') }}: {{ $this->idCard->card_number }}
                            &middot; {{ __('Valid till') }}: {{ $this->idCard->expiry_date?->format('M Y') ?? __('N/A') }}
                        </p>
                    </div>
                </div>
                <flux:button
                    variant="primary"
                    class="button"
                    icon="arrow-down-tray"
                    :href="route('id-cards.download', $this->idCard->id)"
                    target="_blank"
                >
                    {{ __('Download') }}
                </flux:button>
            </div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="identification" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No ID Card') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Your ID card has not been issued yet. Contact the school.') }}</p>
            </div>
        @endif
    </flux:card>

    {{-- Certificates --}}
    <flux:card>
        <flux:heading size="sm" class="font-semibold mb-4">{{ __('Certificates') }}</flux:heading>

        @if($this->certificates->count())
            <div class="space-y-3">
                @foreach($this->certificates as $cert)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-zinc-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                <flux:icon name="document-duplicate" class="size-6" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ ucfirst($cert->type) }} {{ __('Certificate') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ __('No') }}: {{ $cert->certificate_number }}
                                    &middot; {{ __('Issued') }}: {{ $cert->issue_date?->format('M d, Y') }}
                                    @if($cert->purpose)
                                        &middot; {{ $cert->purpose }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <flux:button
                            variant="subtle"
                            icon="arrow-down-tray"
                            :href="route('certificates.download', $cert->id)"
                            target="_blank"
                        >
                            {{ __('Download') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <flux:icon name="document-duplicate" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Certificates') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Certificates issued to you will appear here.') }}</p>
            </div>
        @endif
    </flux:card>
</div>
</div>
