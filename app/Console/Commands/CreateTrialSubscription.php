<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscription:create-trial {institution_id?}')]
#[Description('Create a trial subscription for an institution')]
class CreateTrialSubscription extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $institutionId = $this->argument('institution_id');

        if ($institutionId) {
            $institution = Institution::find($institutionId);
            if (! $institution) {
                $this->error("Institution with ID {$institutionId} not found.");

                return 1;
            }
        } else {
            $institutions = Institution::all();
            if ($institutions->isEmpty()) {
                $this->error('No institutions found.');

                return 1;
            }

            // Display institutions in a table
            $this->table(['ID', 'Name'], $institutions->map(function ($inst) {
                return [$inst->id, $inst->name];
            })->toArray());

            $selectedId = $this->ask('Enter the Institution ID');
            $institution = Institution::find($selectedId);

            if (! $institution) {
                $this->error("Institution with ID {$selectedId} not found.");

                return 1;
            }
        }

        // Check if institution already has a subscription
        if ($institution->currentSubscription()->exists()) {
            $this->error("Institution '{$institution->name}' already has an active subscription.");

            return 1;
        }

        // Get the Professional plan (most popular)
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        if (! $plan) {
            $this->error('Professional plan not found. Please run the SubscriptionPlanSeeder first.');

            return 1;
        }

        // Create trial subscription
        $subscription = Subscription::create([
            'institution_id' => $institution->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
            'amount' => $plan->price,
            'billing_cycle' => 'monthly',
            'plan_features' => $plan->features,
        ]);

        $this->info("Trial subscription created successfully for '{$institution->name}'!");
        $this->info("Plan: {$plan->name}");
        $this->info("Trial ends: {$subscription->trial_ends_at->format('M j, Y')}");

        return 0;
    }
}
