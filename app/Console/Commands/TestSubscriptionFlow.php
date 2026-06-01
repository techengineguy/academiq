<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscription:test-flow')]
#[Description('Test the subscription system flow')]
class TestSubscriptionFlow extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Subscription System Flow...');
        $this->newLine();

        // Test 1: Check if subscription plans exist
        $this->info('1. Checking subscription plans...');
        $plans = SubscriptionPlan::active()->get();
        if ($plans->count() > 0) {
            $this->info("   ✅ Found {$plans->count()} active subscription plans");
            foreach ($plans as $plan) {
                $this->line("      - {$plan->name}: {$plan->formatted_price} {$plan->billing_cycle_label}");
            }
        } else {
            $this->error('   ❌ No subscription plans found. Run: php artisan db:seed --class=SubscriptionPlanSeeder');

            return 1;
        }

        $this->newLine();

        // Test 2: Check institutions
        $this->info('2. Checking institutions...');
        $institutions = Institution::all();
        if ($institutions->count() > 0) {
            $this->info("   ✅ Found {$institutions->count()} institutions");
            foreach ($institutions as $institution) {
                $hasSubscription = $institution->hasActiveSubscription();
                $status = $hasSubscription ? '✅ Active' : '❌ No subscription';
                $this->line("      - {$institution->name}: {$status}");

                if ($hasSubscription) {
                    $subscription = $institution->currentSubscription()->first();
                    if ($subscription) {
                        $this->line("        Plan: {$subscription->plan->name}");
                        $this->line("        Status: {$subscription->status_label}");
                        if ($subscription->isTrial()) {
                            $this->line("        Trial ends: {$subscription->trial_ends_at->format('M j, Y')}");
                        }
                    }
                }
            }
        } else {
            $this->error('   ❌ No institutions found');

            return 1;
        }

        $this->newLine();

        // Test 3: Test subscription access logic
        $this->info('3. Testing subscription access logic...');
        foreach ($institutions as $institution) {
            $this->line("   Testing {$institution->name}:");

            // Test hasActiveSubscription
            $hasActive = $institution->hasActiveSubscription();
            $this->line('      - hasActiveSubscription(): '.($hasActive ? 'true' : 'false'));

            // Test isOnTrial
            $isOnTrial = $institution->isOnTrial();
            $this->line('      - isOnTrial(): '.($isOnTrial ? 'true' : 'false'));

            // Test trialDaysRemaining
            if ($isOnTrial) {
                $daysRemaining = $institution->trialDaysRemaining();
                $this->line("      - trialDaysRemaining(): {$daysRemaining} days");
            }
        }

        $this->newLine();

        // Test 4: Test middleware logic simulation
        $this->info('4. Testing middleware access logic...');
        foreach ($institutions as $institution) {
            $users = $institution->users()->where('role', 'admin')->first();
            if ($users) {
                $wouldHaveAccess = $institution->hasActiveSubscription();
                $status = $wouldHaveAccess ? '✅ Would have access' : '❌ Would be redirected to subscription page';
                $this->line("   {$institution->name} admin: {$status}");
            }
        }

        $this->newLine();

        // Test 5: Check routes
        $this->info('5. Checking subscription routes...');
        $routes = [
            'subscription.plans' => 'Plan selection page',
            'subscription.expired' => 'Expired subscription page',
            'subscription.manage' => 'Subscription management page',
        ];

        foreach ($routes as $routeName => $description) {
            try {
                $url = route($routeName);
                $this->line("   ✅ {$description}: {$url}");
            } catch (\Exception $e) {
                $this->error("   ❌ {$description}: Route not found");
            }
        }

        $this->newLine();
        $this->info('🎉 Subscription system test completed!');

        // Summary
        $activeSubscriptions = Subscription::whereIn('status', ['trial', 'active'])->count();
        $totalInstitutions = $institutions->count();

        $this->newLine();
        $this->info('📊 Summary:');
        $this->line("   - Total institutions: {$totalInstitutions}");
        $this->line("   - Active subscriptions: {$activeSubscriptions}");
        $this->line("   - Available plans: {$plans->count()}");

        if ($activeSubscriptions < $totalInstitutions) {
            $this->newLine();
            $this->warn('💡 Tip: Create trial subscriptions with: php artisan subscription:create-trial');
        }

        return 0;
    }
}
