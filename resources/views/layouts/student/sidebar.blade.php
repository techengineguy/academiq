<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="flex items-center justify-center gap-2 shrink-0">
                <x-app-logo :sidebar="true" href="{{ route('student.dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="layout-grid" :href="route('student.dashboard')" :current="request()->routeIs('student.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="clipboard-document-check" :href="route('student.attendance')" :current="request()->routeIs('student.attendance')" wire:navigate>
                    {{ __('My Attendance') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar" :href="route('student.results')" :current="request()->routeIs('student.results')" wire:navigate>
                    {{ __('My Results') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="receipt-percent" :href="route('student.fees')" :current="request()->routeIs('student.fees')" wire:navigate>
                    {{ __('My Fees') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="document-text" :href="route('student.assignments')" :current="request()->routeIs('student.assignments')" wire:navigate>
                    {{ __('Assignments') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="calendar-days" :href="route('student.timetable')" :current="request()->routeIs('student.timetable')" wire:navigate>
                    {{ __('Timetable') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="megaphone" :href="route('student.announcements')" :current="request()->routeIs('student.announcements')" wire:navigate>
                    {{ __('Announcements') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="calendar" :href="route('student.events')" :current="request()->routeIs('student.events')" wire:navigate>
                    {{ __('Events') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="envelope" :href="route('student.messages')" :current="request()->routeIs('student.messages*')" wire:navigate>
                    {{ __('Messages') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="bell" :href="route('student.notifications')" :current="request()->routeIs('student.notifications')" wire:navigate>
                    {{ __('Notifications') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="document-duplicate" :href="route('student.documents')" :current="request()->routeIs('student.documents')" wire:navigate>
                    {{ __('My Documents') }}
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
                @php
                    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
                    $unreadMessages = \App\Models\Message::where('tenant_id', auth()->user()->tenant_id)->where('receiver_id', auth()->id())->where('is_read', false)->whereNull('parent_message_id')->count();
                    $urgentAnnouncements = \App\Models\Announcement::where('tenant_id', auth()->user()->tenant_id)->where('status', 'published')->whereIn('target_audience', ['all', 'students'])->where('is_urgent', true)->where(function($q) { $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()); })->count();
                    $upcomingEvents = \App\Models\Event::where('tenant_id', auth()->user()->tenant_id)->where('status', 'upcoming')->where('start_date', '>=', now())->count();
                @endphp
                <flux:navbar.item icon="envelope" :href="route('student.messages')" wire:navigate label="Messages" :badge="$unreadMessages > 0 ? $unreadMessages : null" />
                <flux:navbar.item icon="megaphone" :href="route('student.announcements')" wire:navigate label="Announcements" :badge="$urgentAnnouncements > 0 ? $urgentAnnouncements : null" />
                <flux:navbar.item icon="calendar-days" :href="route('student.events')" wire:navigate label="Events" :badge="$upcomingEvents > 0 ? $upcomingEvents : null" />
                <flux:navbar.item icon="bell" :href="route('student.notifications')" wire:navigate label="Notifications" :badge="$unreadNotifications > 0 ? $unreadNotifications : null" />
                <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />
            </flux:navbar>
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
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
