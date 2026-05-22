<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="flex items-center justify-center gap-2 shrink-0">
                <x-app-logo :sidebar="true" href="{{ route('teacher.dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="layout-grid" :href="route('teacher.dashboard')" :current="request()->routeIs('teacher.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="users" :href="route('teacher.my-classes')" :current="request()->routeIs('teacher.my-classes')" wire:navigate>
                    {{ __('My Classes') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="clipboard-document-check" :href="route('teacher.attendance')" :current="request()->routeIs('teacher.attendance')" wire:navigate>
                    {{ __('Attendance') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="document-text" :href="route('teacher.assignments')" :current="request()->routeIs('teacher.assignments')" wire:navigate>
                    {{ __('Assignments') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar" :href="route('teacher.results')" :current="request()->routeIs('teacher.results')" wire:navigate>
                    {{ __('Enter Results') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="list-bullet" :href="route('teacher.lesson-plans')" :current="request()->routeIs('teacher.lesson-plans')" wire:navigate>
                    {{ __('Lesson Plans') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="calendar-days" :href="route('teacher.timetable')" :current="request()->routeIs('teacher.timetable')" wire:navigate>
                    {{ __('My Timetable') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="hand-raised" :href="route('teacher.leave')" :current="request()->routeIs('teacher.leave')" wire:navigate>
                    {{ __('Leave') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->username" />
        </flux:sidebar>

        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
            <flux:text class="text-base max-lg:hidden font-semibold" variant="strong">
                {{ __('Welcome') }}, {{ auth()->user()->first_name }}!
            </flux:text>
            <flux:spacer />
            <flux:navbar class="me-4">
                <flux:navbar.item icon="bell" href="#" label="Notifications" />
                <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />
            </flux:navbar>
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->username" :initials="auth()->user()->initials()" />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
