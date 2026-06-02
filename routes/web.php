<?php

use App\Http\Controllers\Subscription\CallbackController;
use App\Http\Controllers\Subscription\WebhookController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::domain(config('domain.main'))->group(function () {

    // Welcome / Home page
    Route::view('/', 'welcome')->name('home');
    Route::livewire('admissions/apply/{institution:uuid}', 'pages::public.admissions.apply')
        ->name('admissions.apply');
    Route::livewire('admissions/success/{institution:uuid}/{application:uuid}', 'pages::public.admissions.success')
        ->name('admissions.success');
    Route::livewire('invitation/{token}', 'pages::auth.invitation.accept')
        ->name('accountant.invitation.accept');

    // Expired page — visible to all authenticated users so they see why they're blocked
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::livewire('/subscription/expired', 'pages::subscription.expired')->name('subscription.expired');
    });

    // Subscription action pages — admin/accountant only
    Route::middleware(['auth', 'verified', 'subscription.access'])->group(function () {
        Route::livewire('/subscription/plans', 'pages::subscription.plans')->name('subscription.plans');
        Route::livewire('/subscription/checkout', 'pages::subscription.checkout')->name('subscription.checkout');
        Route::livewire('/subscription/manage', 'pages::subscription.manage')->name('subscription.manage')->middleware('subscription');

        // Paystack callback — requires auth so we can resolve the dashboard route
        Route::get('/subscription/callback', CallbackController::class)->name('subscription.callback');
    });

    // Paystack webhook — no auth, signature-verified instead
    Route::post('/subscription/webhook/paystack', WebhookController::class)
        ->name('subscription.webhook.paystack')
        ->withoutMiddleware(ValidateCsrfToken::class);
});

require __DIR__.'/auth.php';
require __DIR__.'/app.php';
require __DIR__.'/settings.php';
