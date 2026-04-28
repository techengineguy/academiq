<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-blue-100 via-blue-50 to-blue-100 dark:from-neutral-950 dark:via-neutral-950 dark:to-neutral-900 dark:bg-gradient-to-b">
        <div class="flex justify-end p-4 md:p-6">
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />
        </div>
        <div class="w-full max-w-lg mx-auto sm:max-w-4xl p-6 md:p-6">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex mb-1 items-center justify-center rounded-md">
                    <x-app-logo-icon />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <div class="">
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
