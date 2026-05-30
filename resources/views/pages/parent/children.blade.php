<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Concerns\ScopesToParentChildren;
use App\Models\Attendance;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\Auth;

new
#[Title('My Children')]
#[Layout('layouts.parent')]
class extends Component {
    use ScopesToParentChildren;

    #[Computed]
    public function children()
    {
        $children = $this->parentChildren();

        return $children->map(function ($child) {
            $total = Attendance::where('student_id', $child->id)->count();
            $present = Attendance::where('student_id', $child->id)->where('status', 'present')->count();
            $balance = (float) FeeInvoice::where('student_id', $child->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance');

            $child->attendance_rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            $child->fee_balance = $balance;

            return $child;
        });
    }
};
?>
<div>
<div class="space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('My Children') }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Overview of all your children at this school.') }}</p>
    </div>

    @if($this->children->count())
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($this->children as $child)
                <flux:card>
                    <div class="flex items-start gap-4">
                        <flux:avatar :name="($child->user?->first_name ?? '') . ' ' . ($child->user?->last_name ?? '')" size="lg" />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $child->user?->first_name }} {{ $child->user?->last_name }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ $child->class?->name ?? '-' }} &middot; {{ $child->section?->name ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ __('Roll') }}: {{ $child->roll_number ?? '-' }} &middot; {{ $child->admission_number ?? '-' }}
                            </p>
                        </div>
                        <flux:badge :color="$child->status === 'active' ? 'green' : 'gray'">
                            {{ ucfirst($child->status) }}
                        </flux:badge>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-zinc-700">
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Attendance') }}</p>
                            <p class="text-lg font-bold text-green-600">{{ $child->attendance_rate }}%</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Pending Fees') }}</p>
                            <p class="text-lg font-bold {{ $child->fee_balance > 0 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                {{ number_format($child->fee_balance, 0) }}
                            </p>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @else
        <flux:card>
            <div class="p-6 text-center">
                <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Children Linked') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Contact the school to link your children to your account.') }}</p>
            </div>
        </flux:card>
    @endif
</div>
</div>
