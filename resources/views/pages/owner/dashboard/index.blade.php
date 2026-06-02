<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Subscription;
use App\Models\Institution;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;

new
#[Title('Owner Dashboard')]
#[Layout('layouts.owner')]
class extends Component {

    public string $revenueRange = 'all_time';

    #[Computed]
    public function totalInstitutions(): int
    {
        return (int) Institution::count();
    }

    #[Computed]
    public function activeSubscriptions(): int
    {
        return (int) Subscription::where('status', 'active')->count();
    }

    #[Computed]
    public function trialSubscriptions(): int
    {
        return (int) Subscription::where('status', 'trial')->count();
    }

    #[Computed]
    public function expiredSubscriptions(): int
    {
        return (int) Subscription::whereIn('status', ['expired', 'cancelled'])->count();
    }

    #[Computed]
    public function mrr(): float
    {
        // Sum of active monthly subscriptions
        $monthly = (float) Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('amount');

        // Yearly → monthly equivalent
        $yearlyMonthly = (float) Subscription::where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->sum('amount') / 12;

        return round($monthly + $yearlyMonthly, 2);
    }

    #[Computed]
    public function arr(): float
    {
        return round($this->mrr * 12, 2);
    }

    #[Computed]
    public function totalRevenue(): float
    {
        $query = Subscription::where('status', 'active');

        if ($this->revenueRange === 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($this->revenueRange === 'this_year') {
            $query->whereYear('created_at', now()->year);
        }

        return (float) $query->sum('amount');
    }

    #[Computed]
    public function revenueByPlan(): \Illuminate\Support\Collection
    {
        return Subscription::where('status', 'active')
            ->selectRaw('subscription_plan_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('subscription_plan_id')
            ->with('plan:id,name')
            ->get()
            ->map(fn ($row) => [
                'plan' => $row->plan?->name ?? 'Unknown',
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ])
            ->sortByDesc('total')
            ->values();
    }

    #[Computed]
    public function monthlyRevenueTrend(): \Illuminate\Support\Collection
    {
        return Subscription::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => Carbon::parse($row->month . '-01')->format('M Y'),
                'total' => (float) $row->total,
            ]);
    }

    #[Computed]
    public function recentSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::with(['institution', 'plan'])
            ->latest()
            ->limit(10)
            ->get();
    }
};
?>
<div class="space-y-8 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Dashboard') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Platform revenue and subscription overview.') }}</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon name="building-office-2" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Institutions') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalInstitutions) }}</p>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="h-5 w-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Active Subscriptions') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->activeSubscriptions) }}</p>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <flux:icon name="arrow-trending-up" class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('MRR') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($this->mrr, 0) }}</p>
                    <p class="text-xs text-gray-400">ARR: ₦{{ number_format($this->arr, 0) }}</p>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Trials') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->trialSubscriptions) }}</p>
                    <p class="text-xs text-gray-400">{{ $this->expiredSubscriptions }} {{ __('expired/cancelled') }}</p>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        {{-- Revenue by Plan --}}
        <flux:card>
            <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">{{ __('Revenue by Plan') }}</h2>
            @if($this->revenueByPlan->isNotEmpty())
                <div class="space-y-3">
                    @php
                        $maxTotal = $this->revenueByPlan->max('total') ?: 1;
                    @endphp
                    @foreach($this->revenueByPlan as $row)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $row['plan'] }}</span>
                                <span class="text-gray-500">
                                    ₦{{ number_format($row['total'], 0) }}
                                    <span class="text-xs text-gray-400">({{ $row['count'] }} {{ __('active') }})</span>
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-700">
                                <div
                                    class="h-2 rounded-full bg-indigo-500"
                                    style="width: {{ round(($row['total'] / $maxTotal) * 100) }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">{{ __('No active subscriptions yet.') }}</p>
            @endif
        </flux:card>

        {{-- Monthly Revenue Trend --}}
        <flux:card>
            <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">{{ __('Monthly Revenue Trend') }}</h2>
            @if($this->monthlyRevenueTrend->isNotEmpty())
                @php
                    $maxTrend = $this->monthlyRevenueTrend->max('total') ?: 1;
                @endphp
                <div class="flex h-32 items-end gap-1">
                    @foreach($this->monthlyRevenueTrend as $month)
                        <div class="group relative flex flex-1 flex-col items-center gap-1">
                            <div
                                class="w-full rounded-t bg-indigo-400 dark:bg-indigo-500 transition-all"
                                style="height: {{ max(4, round(($month['total'] / $maxTrend) * 100)) }}px"
                            ></div>
                            <span class="hidden rotate-45 text-[9px] text-gray-400 group-hover:block">{{ $month['month'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-xs text-gray-400">
                    <span>{{ $this->monthlyRevenueTrend->first()['month'] }}</span>
                    <span>{{ $this->monthlyRevenueTrend->last()['month'] }}</span>
                </div>
            @else
                <p class="text-sm text-gray-400">{{ __('No data yet.') }}</p>
            @endif
        </flux:card>
    </div>

    {{-- Recent Subscriptions --}}
    <flux:card>
        <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">{{ __('Recent Subscriptions') }}</h2>
        @if($this->recentSubscriptions->isNotEmpty())
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Institution') }}</flux:table.column>
                    <flux:table.column>{{ __('Plan') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Amount') }}</flux:table.column>
                    <flux:table.column>{{ __('Cycle') }}</flux:table.column>
                    <flux:table.column>{{ __('Ends') }}</flux:table.column>
                    <flux:table.column>{{ __('Started') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->recentSubscriptions as $sub)
                    <flux:table.rows>
                        <flux:table.row :key="$sub->id">
                            <flux:table.cell>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $sub->institution?->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $sub->plan?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="match($sub->status) {
                                    'active'    => 'green',
                                    'trial'     => 'blue',
                                    'past_due'  => 'amber',
                                    'cancelled' => 'red',
                                    default     => 'gray'
                                }">{{ ucfirst($sub->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>₦{{ number_format((float) $sub->amount, 0) }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst($sub->billing_cycle) }}</flux:table.cell>
                            <flux:table.cell>{{ $sub->ends_at?->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell class="text-gray-400">{{ $sub->created_at->diffForHumans() }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <p class="text-sm text-gray-400">{{ __('No subscriptions yet.') }}</p>
        @endif
    </flux:card>
</div>
