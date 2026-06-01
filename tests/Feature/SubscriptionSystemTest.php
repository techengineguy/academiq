<?php

use App\Models\Institution;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

test('user without subscription is redirected to expired page', function () {
    // Create institution and user without subscription
    $institution = Institution::factory()->create();
    $user = User::factory()->create([
        'institution_id' => $institution->id,
        'tenant_id' => $institution->uuid,
        'role' => 'admin',
    ]);

    // Try to access dashboard
    $response = $this->actingAs($user)->get(route('dashboard'));

    // Should be redirected to subscription expired page
    $response->assertRedirect(route('subscription.expired'));
});

test('user with active trial can access dashboard', function () {
    // Create subscription plan
    $plan = SubscriptionPlan::factory()->create([
        'name' => 'Professional',
        'slug' => 'professional',
        'price' => 35000,
    ]);

    // Create institution and user
    $institution = Institution::factory()->create();
    $user = User::factory()->create([
        'institution_id' => $institution->id,
        'tenant_id' => $institution->uuid,
        'role' => 'admin',
    ]);

    // Create active trial subscription
    Subscription::create([
        'institution_id' => $institution->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(7),
        'starts_at' => now(),
        'ends_at' => now()->addDays(7),
        'amount' => $plan->price,
        'billing_cycle' => 'monthly',
        'plan_features' => ['Feature 1', 'Feature 2'],
    ]);

    // Should be able to access dashboard
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertStatus(200);
});

test('subscription plans page is accessible', function () {
    // Create subscription plans
    SubscriptionPlan::factory()->count(3)->create();

    // Create institution and user
    $institution = Institution::factory()->create();
    $user = User::factory()->create([
        'institution_id' => $institution->id,
        'tenant_id' => $institution->uuid,
        'role' => 'admin',
    ]);

    // Should be able to access plans page
    $response = $this->actingAs($user)->get(route('subscription.plans'));
    $response->assertStatus(200);
    $response->assertSee('Choose Your Plan');
});
