<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <div class="min-h-screen flex flex-col">
            {{-- Header --}}
            <header class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <x-app-logo :sidebar="false" href="{{ route('home') }}" />
                        </div>
                        
                        @auth
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <flux:button type="submit" variant="outline" size="sm">
                                    Logout
                                </flux:button>
                            </form>
                        </div>
                        @else
                        <div class="flex items-center gap-4">
                            <flux:button href="{{ route('login') }}" variant="outline" size="sm">
                                Login
                            </flux:button>
                            <flux:button href="{{ route('register') }}" variant="primary" size="sm">
                                Register
                            </flux:button>
                        </div>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="flex-1">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <footer class="bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
                        © {{ date('Y') }} Academiq. All rights reserved.
                    </div>
                </div>
            </footer>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>