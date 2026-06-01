<?php

namespace App\Observers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

class ActivityObserver
{
    public function handleLogin(Login $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
            ->log('User logged in');
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            activity()
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip()])
                ->log('User logged out');
        }
    }

    public function handleFailed(Failed $event): void
    {
        activity()
            ->withProperties(['email' => $event->credentials['email'] ?? 'unknown', 'ip' => request()->ip()])
            ->log('Failed login attempt');
    }
}
