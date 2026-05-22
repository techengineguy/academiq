<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Observers\UserObserver;

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
            $user = auth()->user();

            if (! $user) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }

            return $user->hasAnyPermission($permissions);
        });

        Blade::if('hasRole', function (string ...$roles): bool {
            $user = auth()->user();

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
    }
}