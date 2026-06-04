<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\UserObserver;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Multitenancy\Models\Tenant;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerMacros();
        $this->registerBladeDirectives();

        User::observe(UserObserver::class);
        Announcement::observe(AnnouncementObserver::class);

        // Log auth events
        $observer = new ActivityObserver;
        Event::listen(Login::class, [$observer, 'handleLogin']);
        Event::listen(Logout::class, [$observer, 'handleLogout']);
        Event::listen(Failed::class, [$observer, 'handleFailed']);
    }

    protected function registerMacros(): void
    {
        Application::macro('domainUrl', function (string $domainName, string $path = ''): string {
            $domain = config("domain.{$domainName}");
            $scheme = $this->isProduction() ? 'https' : 'http';

            return $scheme.'://'.$domain.$path;
        });
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('hasPermission', function (string ...$permissions): bool {
            /** @var User|null $user */
            $user = Auth::user();

            if (! $user) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }

            return $user->hasAnyPermission($permissions);
        });

        Blade::if('hasRole', function (string ...$roles): bool {
            /** @var User|null $user */
            $user = Auth::user();

            if (! $user) {
                return false;
            }

            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false;
        });

        Blade::if('hasFeature', function (string $feature): bool {
            /** @var User|null $user */
            $user = Auth::user();

            $institution = Tenant::current() ?? $user?->institution;

            if (! $user || ! $institution) {
                return false;
            }

            return $institution->hasFeature($feature);
        });
    }
}
