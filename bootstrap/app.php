<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckPlanFeature;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\RedirectUsers;
use App\Http\Middleware\RequireOwnerAccess;
use App\Http\Middleware\RequireSubscriptionAccess;
use App\Http\Middleware\RequiresTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => CheckPermission::class,
            'redirect.users' => RedirectUsers::class,
            'subscription' => CheckSubscription::class,
            'plan.feature' => CheckPlanFeature::class,
            'subscription.access' => RequireSubscriptionAccess::class,
            'owner.access' => RequireOwnerAccess::class,
        ]);

        $middleware->group('tenant', [
            RequiresTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
