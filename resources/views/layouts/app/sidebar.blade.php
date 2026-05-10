<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="flex items-center justify-center gap-2 shrink-0">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <!-- Platform -->
                <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <!-- Academic Management -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('academic-years.*', 'classes.*', 'sections.*', 'subjects.*', 'timetables.*', 'time-slots.*', 'lesson-plans.*', 'academic.trash')" :heading="__('Academic')" class="grid">
                    <flux:sidebar.item icon="calendar" :href="route('academic-years.index')" :current="request()->routeIs('academic-years.*')" wire:navigate>
                        {{ __('Academic Years') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-library" :href="route('classes.index')" :current="request()->routeIs('classes.*')" wire:navigate>
                        {{ __('Classes') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('sections.index')" :current="request()->routeIs('sections.*')" wire:navigate>
                        {{ __('Sections') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="book-open" :href="route('subjects.index')" :current="request()->routeIs('subjects.*')" wire:navigate>
                        {{ __('Subjects') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('timetables.index')" :current="request()->routeIs('timetables.*')" wire:navigate>
                        {{ __('Timetable') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('time-slots.index')" :current="request()->routeIs('time-slots.*')" wire:navigate>
                        {{ __('Time Slots') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="list-bullet" :href="route('lesson-plans.index')" :current="request()->routeIs('lesson-plans.*')" wire:navigate>
                        {{ __('Lesson Plans') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="trash" :href="route('academic.trash')" :current="request()->routeIs('academic.trash')" wire:navigate>
                        {{ __('Trash') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Student Management -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('students.*', 'admission-applications.*', 'scholarships.*', 'scholarship-awards.*', 'promotions.*')" :heading="__('Students')" class="grid">
                    <flux:sidebar.item icon="users" :href="route('students.index')" :current="request()->routeIs('students.*')" wire:navigate>
                        {{ __('Students') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" :href="route('admission-applications.index')" :current="request()->routeIs('admission-applications.*')" wire:navigate>
                        {{ __('Admissions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="trophy" :href="route('scholarships.index')" :current="request()->routeIs('scholarships.*')" wire:navigate>
                        {{ __('Scholarships') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="star" :href="route('scholarship-awards.index')" :current="request()->routeIs('scholarship-awards.*')" wire:navigate>
                        {{ __('Award Scholarships') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-up-right" :href="route('promotions.index')" :current="request()->routeIs('promotions.*')" wire:navigate>
                        {{ __('Promotions') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Staff Management -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('teachers.*', 'staffs.*', 'payroll.*', 'staff.trash')" :heading="__('Staff')" class="grid">
                    <flux:sidebar.item icon="academic-cap" :href="route('teachers.index')" :current="request()->routeIs('teachers.*')" wire:navigate>
                        {{ __('Teachers') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="briefcase" :href="route('staffs.index')" :current="request()->routeIs('staffs.*')" wire:navigate>
                        {{ __('Staff') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('payroll.index')" :current="request()->routeIs('payroll.*')" wire:navigate>
                        {{ __('Payroll') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="trash" :href="route('staff.trash')" :current="request()->routeIs('staff.trash')" wire:navigate>
                        {{ __('Staff Trash') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Attendance -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('attendance.*', 'staff-attendance.*')" :heading="__('Attendance')" class="grid">
                    <flux:sidebar.item icon="clipboard-document-check" :href="route('attendance.index')" :current="request()->routeIs('attendance.*')" wire:navigate>
                        {{ __('Student Attendance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="check-circle" :href="route('staff-attendance.index')" :current="request()->routeIs('staff-attendance.*')" wire:navigate>
                        {{ __('Staff Attendance') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Exams & Results -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('exams.*', 'exam-schedules.*', 'results.*', 'grade-scales.*')" :heading="__('Exams')" class="grid">
                    <flux:sidebar.item icon="pencil-square" :href="route('exams.index')" :current="request()->routeIs('exams.*')" wire:navigate>
                        {{ __('Exams') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('exam-schedules.index')" :current="request()->routeIs('exam-schedules.*')" wire:navigate>
                        {{ __('Schedules') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('results.index')" :current="request()->routeIs('results.*')" wire:navigate>
                        {{ __('Results') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="scale" :href="route('grade-scales.index')" :current="request()->routeIs('grade-scales.*')" wire:navigate>
                        {{ __('Grade Scales') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Fee Management -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('fee-types.*', 'fee-structures.*', 'fee-invoices.*', 'fee-payments.*')" :heading="__('Fees')" class="grid">
                    <flux:sidebar.item icon="tag" :href="route('fee-types.index')" :current="request()->routeIs('fee-types.*')" wire:navigate>
                        {{ __('Fee Types') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="table-cells" :href="route('fee-structures.index')" :current="request()->routeIs('fee-structures.*')" wire:navigate>
                        {{ __('Fee Structure') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('fee-invoices.index')" :current="request()->routeIs('fee-invoices.*')" wire:navigate>
                        {{ __('Invoices') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wallet" :href="route('fee-payments.index')" :current="request()->routeIs('fee-payments.*')" wire:navigate>
                        {{ __('Payments') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Assignments -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('assignments.*', 'submissions.*')" :heading="__('Assignments')" class="grid">
                    <flux:sidebar.item icon="document-plus" :href="route('assignments.index')" :current="request()->routeIs('assignments.*')" wire:navigate>
                        {{ __('Assignments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-check" :href="route('submissions.index')" :current="request()->routeIs('submissions.*')" wire:navigate>
                        {{ __('Submissions') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Leave Management -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('leave-types.*', 'leave-applications.*')" :heading="__('Leave')" class="grid">
                    <flux:sidebar.item icon="document-text" :href="route('leave-types.index')" :current="request()->routeIs('leave-types.*')" wire:navigate>
                        {{ __('Leave Types') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="hand-raised" :href="route('leave-applications.index')" :current="request()->routeIs('leave-applications.*')" wire:navigate>
                        {{ __('Applications') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Hostel Management -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('hostel-buildings.*', 'hostel-rooms.*', 'hostel-allocations.*', 'hostel-visitors.*')" :heading="__('Hostel')" class="grid">
                    <flux:sidebar.item icon="building-office" :href="route('hostel-buildings.index')" :current="request()->routeIs('hostel-buildings.*')" wire:navigate>
                        {{ __('Buildings') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="squares-2x2" :href="route('hostel-rooms.index')" :current="request()->routeIs('hostel-rooms.*')" wire:navigate>
                        {{ __('Rooms') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="key" :href="route('hostel-allocations.index')" :current="request()->routeIs('hostel-allocations.*')" wire:navigate>
                        {{ __('Allocations') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('hostel-visitors.index')" :current="request()->routeIs('hostel-visitors.*')" wire:navigate>
                        {{ __('Visitors') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Communications -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('announcements.*', 'events.*', 'messages.*', 'notifications.*')" :heading="__('Communications')" class="grid">
                    <flux:sidebar.item icon="megaphone" :href="route('announcements.index')" :current="request()->routeIs('announcements.*')" wire:navigate>
                        {{ __('Announcements') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('events.index')" :current="request()->routeIs('events.*')" wire:navigate>
                        {{ __('Events') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>
                        {{ __('Notifications') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Documents -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('certificates.*', 'id-cards.*', 'document-templates.*')" :heading="__('Documents')" class="grid">
                    <flux:sidebar.item icon="document-duplicate" :href="route('certificates.index')" :current="request()->routeIs('certificates.*')" wire:navigate>
                        {{ __('Certificates') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="identification" :href="route('id-cards.index')" :current="request()->routeIs('id-cards.*')" wire:navigate>
                        {{ __('ID Cards') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('document-templates.index')" :current="request()->routeIs('document-templates.*')" wire:navigate>
                        {{ __('Templates') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- More -->
                <flux:sidebar.group expandable :expanded="request()->routeIs('complaints.*', 'backups.*', 'activity-logs.*')" :heading="__('More')" class="grid">
                    <flux:sidebar.item icon="exclamation-circle" :href="route('complaints.index')" :current="request()->routeIs('complaints.*')" wire:navigate>
                        {{ __('Complaints') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="archive-box" :href="route('backups.index')" :current="request()->routeIs('backups.*')" wire:navigate>
                        {{ __('Backups') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('activity-logs.index')" :current="request()->routeIs('activity-logs.*')" wire:navigate>
                        {{ __('Activity Logs') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->username" />
        </flux:sidebar>
       
        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
            <flux:text class="text-base max-lg:hidden font-semibold capitalize" variant="strong">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->username }}!</flux:text>
            {{-- <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="home" href="#" current>Home</flux:navbar.item>
                <flux:navbar.item icon="inbox" badge="12" href="#">Inbox</flux:navbar.item>
                <flux:navbar.item icon="document-text" href="#">Documents</flux:navbar.item>
                <flux:navbar.item icon="calendar" href="#">Calendar</flux:navbar.item>
                <flux:separator vertical variant="subtle" class="my-2"/>
                <flux:dropdown class="max-lg:hidden">
                    <flux:navbar.item icon:trailing="chevron-down">Favorites</flux:navbar.item>
                    <flux:navmenu>
                        <flux:navmenu.item href="#">Marketing site</flux:navmenu.item>
                        <flux:navmenu.item href="#">Android app</flux:navmenu.item>
                        <flux:navmenu.item href="#">Brand guidelines</flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>
            </flux:navbar> --}}
            <flux:spacer />
            <flux:navbar class="me-4">
                <flux:navbar.item icon="magnifying-glass" href="#" label="Search" />
                <flux:navbar.item icon="bell" href="#" label="Notifications" />
                <flux:navbar.item class="max-lg:hidden" icon="cog-6-tooth" href="#" label="Settings" />
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
                                <flux:avatar
                                    :name="auth()->user()->username"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->username }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
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
