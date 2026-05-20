<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @include('partials.head')
    <style>
        @keyframes fadeUp { from { opacity:0; transform:translateY(28px) } to { opacity:1; transform:translateY(0) } }
        @keyframes fadeDown { from { opacity:0; transform:translateY(-14px) } to { opacity:1; transform:translateY(0) } }
        @keyframes marquee { from { transform:translateX(0) } to { transform:translateX(-50%) } }
        @keyframes pulse-beacon { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.5)} }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        @keyframes typewriter { from { width: 0 } to { width: 100% } }
        @keyframes caret { 0%,100% { border-right-color: transparent } 50% { border-right-color: #18181b } }
        @keyframes slide-in-mobile { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .animate-fade-up { animation: fadeUp .75s cubic-bezier(.22,1,.36,1) both; }
        .animate-fade-down { animation: fadeDown .6s cubic-bezier(.22,1,.36,1) both; }
        .animate-marquee { animation: marquee 36s linear infinite; width: max-content; }
        .animate-float { animation: float 5s ease-in-out infinite; }
        .pulse-beacon { animation: pulse-beacon 2.2s infinite; }

        .reveal { opacity:0; transform:translateY(22px); transition: opacity .65s cubic-bezier(.22,1,.36,1), transform .65s cubic-bezier(.22,1,.36,1); }
        .reveal.visible { opacity:1; transform:translateY(0); }

        .shine {
            background: linear-gradient(120deg, #4f46e5 0%, #818cf8 40%, #6d28d9 80%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .grid-bg {
            background-image: linear-gradient(#e8e8f0 1px, transparent 1px), linear-gradient(90deg, #e8e8f0 1px, transparent 1px);
            background-size: 56px 56px;
        }

        .typewriter-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.08em;
            width: 100%;
        }

        .type-line {
            display: block;
            overflow: hidden;
            white-space: nowrap;
            width: 0;
            text-align: center;
            animation-fill-mode: forwards;
        }

        .type-line-1 {
            animation: typewriter 1.1s steps(22, end) .15s forwards, caret .8s step-end .15s 3;
        }

        .type-line-2 {
            animation: typewriter 1.35s steps(28, end) 1.3s forwards, caret .8s step-end 1.3s infinite;
        }

        /* Staggered: start after headline typing (~2.65s) */
        .animate-slide-in-right { display:inline-block; will-change: transform, opacity; animation: slide-in-right .95s cubic-bezier(.22,1,.36,1) 2.8s both; }
        .animate-slide-in-left { display:inline-block; will-change: transform, opacity; animation: slide-in-left .95s cubic-bezier(.22,1,.36,1) 2.8s both; }

        @media (max-width: 640px) {
            .type-line {
                white-space: normal;
                width: auto;
                border-right: 0;
                animation: slide-in-mobile .6s ease .12s both;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .type-line {
                white-space: normal;
                width: auto;
                border-right: 0;
                animation: none;
            }
        }
    </style>
</head>
<body class="min-h-screen antialiased bg-white text-zinc-900 overflow-x-hidden">

{{-- HEADER --}}
<flux:header sticky container class="z-50 bg-gradient-to-r from-indigo-50/90 via-white to-zinc-50 border-zinc-200">
    <x-app-logo :sidebar="false" href="{{ route('home') }}" wire:navigate />
    <flux:spacer />
    <flux:navbar class="-mb-px max-lg:hidden flex justify-center grow">
        <flux:navbar.item href="#features">Features</flux:navbar.item>
        <flux:navbar.item href="#how-it-works">How It Works</flux:navbar.item>
        <flux:navbar.item href="#pricing">Pricing</flux:navbar.item>
        <flux:navbar.item href="#faq">FAQ</flux:navbar.item>
    </flux:navbar>
    <flux:spacer />
    <flux:navbar>
        <flux:navbar.item href="{{ route('login') }}">Login</flux:navbar.item>
        <flux:button variant="primary" class="max-lg:hidden button" href="{{ route('register') }}">
            Get started
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </flux:button>
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
    </flux:navbar>
</flux:header>

{{-- MOBILE SIDEBAR --}}
<flux:sidebar sticky collapsible="mobile" class="lg:hidden bg-zinc-50 border-r border-zinc-200">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
        <flux:sidebar.collapse />
    </flux:sidebar.header>
    <flux:sidebar.nav>
        <flux:sidebar.item href="#features">Features</flux:sidebar.item>
        <flux:sidebar.item href="#how-it-works">How It Works</flux:sidebar.item>
        <flux:sidebar.item href="#pricing">Pricing</flux:sidebar.item>
        <flux:sidebar.item href="#faq">FAQ</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('login') }}">Login</flux:sidebar.item>
        <flux:button variant="primary" class="button" href="{{ route('register') }}">Get started</flux:button>
    </flux:sidebar.nav>
</flux:sidebar>

{{-- ═══════════════════════════════════════════
     HERO
═══════════════════════════════════════════ --}}
<section class="relative pb-0 px-6 overflow-hidden">
    {{-- Grid background --}}
    <div class="grid-bg absolute inset-0 pointer-events-none opacity-50"></div>

    {{-- Gradient orbs --}}
    <div class="absolute top-[-160px] left-1/2 -translate-x-1/2 w-[700px] h-[500px] rounded-full blur-[80px] bg-indigo-500/[.13] pointer-events-none"></div>
    <div class="absolute top-16 -right-20 w-[400px] h-[400px] rounded-full blur-[80px] bg-violet-700/[.08] pointer-events-none"></div>
    <div class="absolute bottom-0 -left-20 w-[350px] h-[350px] rounded-full blur-[80px] bg-indigo-400/[.06] pointer-events-none"></div>

    <div class="relative z-10 max-w-[900px] mx-auto text-center">
        {{-- Badge --}}
        <div class="animate-fade-down my-8">
            <span class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-indigo-500/[.07] border border-indigo-500/[.18] text-[.72rem] font-bold text-indigo-600 uppercase tracking-wider">
                <span class="relative flex">
                    <span class="pulse-beacon absolute inset-0 rounded-full bg-emerald-500 opacity-60"></span>
                    <span class="w-[7px] h-[7px] rounded-full bg-emerald-500 shrink-0"></span>
                </span>
                Now Available for Schools Everywhere
            </span>
        </div>

        {{-- Headline — h1 for correct SEO semantics --}}
        <h1 class="animate-fade-up text-[clamp(2.25rem,5vw,4rem)] font-bold leading-[1.06] tracking-[-0.04em] text-zinc-900 mb-6">
            <span class="typewriter-wrap">
                <span class="type-line type-line-1">The complete platform</span>
                <span class="type-line type-line-2">for <span class="shine font-bold">managing your school.</span></span>
            </span>
        </h1>

        {{-- Subheadline — single animation, no nested wrapper --}}
        <p class="animate-fade-up [animation-delay:2800ms] text-lg text-zinc-500 max-w-[560px] mx-auto mb-10 leading-relaxed">
            From admissions to exams, fee collection to payroll. Academiq runs every workflow on one connected, beautifully designed platform.
        </p>

        {{-- CTAs — primary + outline variant --}}
        <div class="animate-fade-up [animation-delay:3000ms] flex flex-wrap gap-3 justify-center items-center mb-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white text-sm font-bold rounded-xl no-underline shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                Start free trial
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="#features" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-zinc-700 text-sm font-semibold rounded-xl no-underline border border-zinc-200 shadow-sm hover:border-zinc-300 hover:bg-zinc-50 hover:-translate-y-0.5 transition-all">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                Explore features
            </a>
        </div>

        <p class="animate-fade-up [animation-delay:3100ms] text-xs text-zinc-400">14-day free trial · No credit card · Cancel anytime</p>

        {{-- Social proof --}}
        <div class="animate-fade-up [animation-delay:3200ms] flex items-center justify-center gap-3.5 mt-8 mb-14">
            <div class="flex">
                @foreach(['bg-indigo-600','bg-emerald-500','bg-amber-500','bg-violet-500'] as $i => $bg)
                <div class="w-8 h-8 rounded-full {{ $bg }} border-[2.5px] border-white flex items-center justify-center text-white text-[.625rem] font-extrabold {{ $i > 0 ? '-ml-2' : '' }} shadow-sm">{{ ['AO','FK','NB','EM'][$i] }}</div>
                @endforeach
            </div>
            <div class="text-sm text-zinc-500">
                <span class="font-bold text-zinc-900">500+</span> institutions worldwide
                <span class="inline-flex items-center gap-1 ml-2 text-amber-500">
                    ★★★★★ <span class="text-zinc-500 font-semibold">4.9/5</span>
                </span>
            </div>
        </div>
    </div>
</section>

{{-- ── App Mockup ── --}}
<div class="animate-fade-up [animation-delay:3300ms] relative z-10 max-w-[1100px] mx-auto mb-0">
    {{-- Float wrapper separated so fade-up doesn't fight float --}}
    <div class="animate-float">
        <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-2xl shadow-zinc-900/[.12]">
            {{-- Chrome bar --}}
            <div class="bg-zinc-50 border-b border-zinc-200 flex items-center gap-3 px-5 py-3.5">
                <div class="flex gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#fc5c57]"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#fdbc40]"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#34c84a]"></span>
                </div>
                <div class="flex-1 flex justify-center">
                    <div class="bg-white border border-zinc-200 rounded-lg px-4 py-1.5 text-[.7rem] text-zinc-400 max-w-[260px] w-full text-center">app.academiq.ng/dashboard</div>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[.65rem] text-zinc-400 font-medium">Secure</span>
                </div>
            </div>

            <div class="min-h-[503px]">
                <div class="bg-[#f9f9fc] p-6">
                    <img src="{{ asset('images/dashboard.svg') }}" class="max-lg:hidden w-full" width="1100" height="503" alt="Academiq dashboard preview" />
                    <img src="{{ asset('images/dashboard.svg') }}" class="lg:hidden w-full" width="500" height="220" alt="Academiq dashboard preview" />
                </div>
            </div>
        </div>
    </div>
    {{-- Fade bottom --}}
    <div class="absolute bottom-0 inset-x-0 h-24 bg-gradient-to-t from-white to-transparent pointer-events-none rounded-b-2xl"></div>
</div>

{{-- ═══════════════════════════════════════════
     LOGOS TICKER
═══════════════════════════════════════════ --}}
<div class="relative py-16 border-t border-b border-zinc-200 bg-gradient-to-b from-zinc-50 to-white overflow-hidden mt-20">
    <p class="text-center text-[.65rem] font-extrabold tracking-[.16em] uppercase text-zinc-400 mb-9">Trusted by leading institutions around the world</p>
    <div class="overflow-hidden relative">
        <div class="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-zinc-50 to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
        <div class="animate-marquee flex items-center gap-14">
            @foreach(array_merge(
                ['Greenfield Academy','Lagos International School','Citadel Academy','Sunrise College','Hillcrest Secondary','St. Michael\'s School','Brightfield Institute','Apex Academy'],
                ['Greenfield Academy','Lagos International School','Citadel Academy','Sunrise College','Hillcrest Secondary','St. Michael\'s School','Brightfield Institute','Apex Academy']
            ) as $name)
            <span class="flex items-center gap-2.5 text-[.68rem] font-extrabold text-zinc-300 tracking-[.15em] uppercase whitespace-nowrap hover:text-zinc-400 transition-colors">
                <span class="w-1 h-1 rounded-full bg-zinc-300 shrink-0"></span>
                {{ $name }}
            </span>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     STATS BAND
═══════════════════════════════════════════ --}}
<div class="py-20 px-6 bg-indigo-600 relative overflow-hidden">
    {{-- Subtle grid overlay --}}
    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px); background-size: 48px 48px;"></div>
    {{-- Glow --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[300px] rounded-full bg-[radial-gradient(ellipse,rgba(255,255,255,.1),transparent_70%)] pointer-events-none"></div>

    <div class="relative w-full">
        <div class="grid grid-cols-4 max-md:grid-cols-2 divide-x divide-white/10 max-md:divide-y max-md:divide-x-0">
            @foreach([
                ['500+', 'Institutions', 'Across 30+ countries'],
                ['98k+', 'Students', 'Actively managed'],
                ['₦2.4B+', 'Fees Processed', 'This academic year'],
                ['99.9%', 'Always Available', 'Reliable, always-on'],
            ] as [$stat, $label, $sub])
            <div class="px-8 py-12 flex flex-col items-center text-center hover:bg-white/[.05] transition-colors">
                <div class="text-[2.75rem] font-black text-white tracking-tighter leading-none mb-2">{{ $stat }}</div>
                <div class="text-sm font-bold text-white/90 mb-1">{{ $label }}</div>
                <div class="text-xs text-white/50">{{ $sub }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     FEATURES
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 relative overflow-hidden" id="features">
    {{-- Subtle background decoration --}}
    <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full bg-indigo-500/[.03] blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-violet-500/[.04] blur-[80px] pointer-events-none"></div>

    <div class="relative max-w-[1200px] mx-auto">
        <div class="text-center mb-16 reveal">
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-600 mb-4">
                <span class="block w-6 h-[1.5px] bg-indigo-600 rounded-full"></span> Features
            </div>
            <h2 class="text-[clamp(2rem,4vw,3.25rem)] font-bold tracking-tight leading-tight text-zinc-900 mb-4">
                Everything your school <span class="shine">needs</span>,<br>nothing it doesn't
            </h2>
            <p class="text-lg text-zinc-500 max-w-[500px] mx-auto leading-relaxed">From the first admission to the final exam result — every step of your institution's workflow, beautifully managed.</p>
        </div>

        <div class="grid grid-cols-3 max-lg:grid-cols-2 max-sm:grid-cols-1 gap-5">
            @foreach([
                ['users', 'bg-indigo-500/10', 'text-indigo-600', 'border-indigo-500/20', 'Student Management', 'Complete student profiles, admission tracking, parent relationships, medical records, and class promotion history.'],
                ['clipboard-document-list', 'bg-emerald-500/10', 'text-emerald-600', 'border-emerald-500/20', 'Exam & Grading', 'Create exams, schedule timetables, record results and auto-calculate grades with configurable grade scales.'],
                ['banknotes', 'bg-amber-500/10', 'text-amber-600', 'border-amber-500/20', 'Fee Management', 'Generate invoices, track payments, manage fee structures per class, handle partial payments and produce reports.'],
                ['check-badge', 'bg-red-500/10', 'text-red-600', 'border-red-500/20', 'Attendance Tracking', 'Daily student and staff attendance with absence notifications, reports, and permission-based access.'],
                ['building-office', 'bg-violet-500/10', 'text-violet-600', 'border-violet-500/20', 'Hostel Management', 'Manage dormitory buildings, rooms, allocations, visitor tracking, and warden assignments.'],
                ['chat-bubble-left-right', 'bg-sky-500/10', 'text-sky-600', 'border-sky-500/20', 'Communications Hub', 'Broadcast announcements, events, internal messages, and push notifications institution-wide.'],
                ['document-text', 'bg-amber-500/10', 'text-amber-600', 'border-amber-500/20', 'Document Generation', 'Generate certificates, ID cards, and custom templates — print-ready and beautifully formatted.'],
                ['briefcase', 'bg-indigo-500/10', 'text-indigo-600', 'border-indigo-500/20', 'Staff & Payroll', 'Teacher profiles, monthly payroll, allowances, deductions, tax calculation, and payment tracking.'],
                ['lock-closed', 'bg-red-500/10', 'text-red-600', 'border-red-500/20', 'Role-Based Access', '6 user types — Admin, Teacher, Student, Parent, Staff, and Accountant — each sees only what they need.'],
            ] as [$icon, $iconBg, $iconCol, $iconBorder, $title, $desc])
            <div class="reveal group relative bg-white border border-zinc-200 rounded-2xl p-6 hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-500/[.08] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                {{-- Hover glow --}}
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/[.02] to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-2xl"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl {{ $iconBg }} border {{ $iconBorder }} flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                        <flux:icon :name="$icon" class="w-5 h-5 {{ $iconCol }}" />
                    </div>
                    <h3 class="text-[.925rem] font-bold text-zinc-900 mb-2 tracking-tight">{{ $title }}</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">{{ $desc }}</p>
                    <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold {{ $iconCol }} opacity-0 group-hover:opacity-100 transition-opacity">
                        Learn more <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-zinc-950 relative overflow-hidden" id="how-it-works">
    {{-- Background decoration --}}
    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px); background-size: 52px 52px;"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] rounded-full bg-[radial-gradient(ellipse,rgba(79,70,229,.18),transparent_65%)] pointer-events-none"></div>

    <div class="relative max-w-[1200px] mx-auto">
        <div class="text-center mb-16 reveal">
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-400 mb-4">
                <span class="block w-6 h-[1.5px] bg-indigo-400 rounded-full"></span> How It Works
            </div>
            <h2 class="text-[clamp(2rem,4vw,3.25rem)] font-bold tracking-tight leading-tight text-white mb-4">
                Up and running <span class="shine">in minutes</span>
            </h2>
            <p class="text-lg text-white/50 max-w-[420px] mx-auto leading-relaxed">No complex onboarding. No lengthy setup. Start managing your institution right away.</p>
        </div>

        <div class="grid grid-cols-3 max-md:grid-cols-1 gap-6 relative">
            {{-- Connector line (desktop only) --}}
            <div class="absolute top-[2.75rem] left-[calc(16.67%+1.5rem)] right-[calc(16.67%+1.5rem)] h-px bg-gradient-to-r from-indigo-500/30 via-indigo-500/60 to-indigo-500/30 max-md:hidden pointer-events-none"></div>

            @foreach([
                ['01','Create Your Institution','Sign up and enter your school details. Your private workspace is ready instantly — your data is completely separate from every other school on the platform.','building-office-2'],
                ['02','Configure & Customize','Set up academic years, classes, subjects, fee structures, and grade scales. Upload your logo and set your academic calendar.','adjustments-horizontal'],
                ['03','Invite Your Team','Add teachers, staff, and accountants — each with the right level of access. Import students and parents in bulk with a simple upload.','user-plus'],
            ] as [$num,$title,$desc,$icon])
            <div class="reveal relative bg-white/[.04] border border-white/[.08] rounded-2xl p-8 hover:bg-white/[.07] hover:border-indigo-500/30 transition-all duration-300 group">
                <div class="absolute top-2 right-4 text-[5.5rem] font-black text-white/[.03] leading-none select-none tracking-tight">{{ $num }}</div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white text-sm font-black mb-6 shadow-lg shadow-indigo-600/40 group-hover:scale-110 transition-transform duration-300 relative z-10">
                        {{ $num }}
                    </div>
                    <h3 class="text-base font-bold text-white mb-2.5 tracking-tight">{{ $title }}</h3>
                    <p class="text-sm text-white/50 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>



{{-- ═══════════════════════════════════════════
     MULTI-TENANT SPLIT
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-white">
    <div class="max-w-[1200px] mx-auto grid grid-cols-2 max-lg:grid-cols-1 gap-20 items-center reveal">
        <div>
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-600 mb-4">
                <span class="block w-6 h-[1.5px] bg-indigo-600 rounded-full"></span> Your School, Your Data
            </div>
            <h2 class="text-[clamp(1.8rem,3.5vw,3rem)] font-bold tracking-tight leading-tight text-zinc-900 mb-5">
                One platform.<br><span class="shine">Every school</span><br>keeps its own private space.
            </h2>
            <p class="text-base text-zinc-500 leading-relaxed mb-8">Every school on Academiq operates in its own private space. Your students, staff, and finances are never visible to anyone else — complete privacy by design.</p>
            <ul class="space-y-4">
                @foreach([
                    ['Each school has its own students, staff, finances, and settings', 'building-office'],
                    ['Your data is completely private and never mixed with other schools', 'shield-check'],
                    ['Switch between schools instantly from a single admin login', 'arrows-right-left'],
                    ['Bank-level privacy — your school\'s data stays yours, always', 'lock-closed'],
                ] as [$item, $icon])
                <li class="flex items-start gap-3.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0 mt-0.5">
                        <flux:icon :name="$icon" class="w-4 h-4 text-emerald-600" />
                    </span>
                    <span class="text-sm text-zinc-500 leading-relaxed pt-1.5">{{ $item }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Institution Switcher UI --}}
        <div class="relative">
            {{-- Glow behind card --}}
            <div class="absolute -inset-4 bg-indigo-500/[.06] rounded-[2.5rem] blur-2xl pointer-events-none"></div>
            <div class="relative bg-white border border-zinc-200 rounded-3xl p-7 shadow-2xl shadow-zinc-900/[.1]">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-[.65rem] font-extrabold tracking-widest uppercase text-zinc-400">Institution Switcher</p>
                    <span class="text-[.6rem] font-bold px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">3 Active</span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-3.5 bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl px-4 py-4 shadow-lg shadow-indigo-600/30">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white text-xs font-black shrink-0">GA</div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-white">Greenfield Academy</p>
                            <p class="text-[.7rem] text-white/70">1,284 students · 86 staff</p>
                        </div>
                        <span class="flex items-center gap-1.5 text-[.65rem] font-bold px-3 py-1 rounded-lg bg-white/20 text-white">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                        </span>
                    </div>
                    @foreach([['SC','Sunrise College','642 students · 41 staff','bg-emerald-500/10','text-emerald-600'],['CA','Citadel Academy','890 students · 63 staff','bg-violet-500/10','text-violet-600']] as [$init,$name,$meta,$bg,$col])
                    <div class="flex items-center gap-3.5 border border-zinc-200 rounded-2xl px-4 py-3.5 hover:border-indigo-300 hover:bg-indigo-500/[.03] transition-all cursor-pointer group">
                        <div class="w-10 h-10 rounded-xl {{ $bg }} {{ $col }} flex items-center justify-center text-xs font-black shrink-0">{{ $init }}</div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-zinc-900">{{ $name }}</p>
                            <p class="text-[.7rem] text-zinc-400">{{ $meta }}</p>
                        </div>
                        <svg class="w-4 h-4 text-zinc-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-center gap-2 border border-dashed border-zinc-200 rounded-2xl py-3.5 text-sm text-zinc-400 cursor-pointer hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-500/[.03] transition-all">
                        <span class="w-5 h-5 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 text-base leading-none">+</span>
                        Add New Institution
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     SECURITY & AUDIT
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-zinc-50 border-t border-b border-zinc-100">
    <div class="max-w-[1200px] mx-auto grid grid-cols-2 max-lg:grid-cols-1 gap-20 items-center reveal">
        {{-- Audit Log UI --}}
        <div class="relative">
            <div class="absolute -inset-4 bg-emerald-500/[.05] rounded-[2.5rem] blur-2xl pointer-events-none"></div>
            <div class="relative bg-white border border-zinc-200 rounded-3xl p-7 shadow-2xl shadow-zinc-900/[.08]">
                <div class="flex items-center justify-between mb-5">
                    <p class="text-[.65rem] font-extrabold tracking-widest uppercase text-zinc-400">Activity Log</p>
                    <div class="flex items-center gap-1.5 text-[.65rem] font-bold text-emerald-600 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-lg">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-beacon"></span> Live
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach([
                        ['LOGIN','Admin logged in from 197.210.x.x','2 min ago','bg-emerald-500/10','text-emerald-600','border-emerald-500/20'],
                        ['ADDED','Student Adaeze Okafor admitted','14 min ago','bg-indigo-500/10','text-indigo-600','border-indigo-500/20'],
                        ['UPDATED','Fee structure updated for SS1','1 hr ago','bg-amber-500/10','text-amber-600','border-amber-500/20'],
                        ['REMOVED','Draft invoice #INV-0821 discarded','3 hrs ago','bg-red-500/10','text-red-600','border-red-500/20'],
                        ['EXPORTED','Term 1 results PDF exported','5 hrs ago','bg-violet-500/10','text-violet-600','border-violet-500/20'],
                    ] as [$action,$text,$time,$bg,$col,$border])
                    <div class="flex items-center gap-3 bg-zinc-50/80 border border-zinc-100 rounded-xl px-3.5 py-3 hover:bg-white hover:border-zinc-200 transition-colors">
                        <span class="text-[.58rem] font-extrabold tracking-wider px-2.5 py-1 rounded-lg {{ $bg }} {{ $col }} border {{ $border }} whitespace-nowrap shrink-0">{{ $action }}</span>
                        <span class="text-[.775rem] text-zinc-600 flex-1 min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">{{ $text }}</span>
                        <span class="text-[.68rem] text-zinc-400 whitespace-nowrap shrink-0 font-medium">{{ $time }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-zinc-100 flex items-center justify-between">
                    <span class="text-xs text-zinc-400">Showing last 5 of 2,847 events</span>
                    <span class="text-xs font-semibold text-indigo-600 cursor-pointer hover:underline">View all →</span>
                </div>
            </div>
        </div>

        <div>
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-600 mb-4">
                <span class="block w-6 h-[1.5px] bg-indigo-600 rounded-full"></span> Security & Audit
            </div>
            <h2 class="text-[clamp(1.8rem,3.5vw,3rem)] font-bold tracking-tight leading-tight text-zinc-900 mb-5">
                Every action.<br><span class="shine">Fully logged</span><br>and traceable.
            </h2>
            <p class="text-base text-zinc-500 leading-relaxed mb-8">Complete security with a full activity history, two-step login, and access controls that keep your school's sensitive data protected at all times.</p>
            <ul class="space-y-4">
                @foreach([
                    ['Two-step login (2FA) for all users — students, staff, and parents', 'device-phone-mobile'],
                    ['Complete activity history — who did what, and when', 'clipboard-document-list'],
                    ['Deleted records are recoverable — nothing is permanently lost by accident', 'archive-box'],
                    ['Protected against common web attacks and data breaches', 'shield-check'],
                ] as [$item, $icon])
                <li class="flex items-start gap-3.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center shrink-0 mt-0.5">
                        <flux:icon :name="$icon" class="w-4 h-4 text-indigo-600" />
                    </span>
                    <span class="text-sm text-zinc-500 leading-relaxed pt-1.5">{{ $item }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════
     USER ROLES
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-zinc-50 border-t border-b border-zinc-100">
    <div class="max-w-[1200px] mx-auto">
        <div class="text-center mb-14 reveal">
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-600 mb-4 justify-center">
                <span class="block w-6 h-[1.5px] bg-indigo-600 rounded-full"></span> User Roles
            </div>
            <h2 class="text-[clamp(2rem,4vw,3.25rem)] font-bold tracking-tight leading-tight text-zinc-900 mb-3.5">
                Built for <span class="shine">everyone</span><br>in your institution
            </h2>
            <p class="text-base text-zinc-500 max-w-[440px] mx-auto">Everyone in your school gets a personalised experience with access to exactly what they need.</p>
        </div>

        <div class="grid grid-cols-3 max-lg:grid-cols-2 max-sm:grid-cols-1 gap-5">
            @foreach([
                ['shield-check','bg-indigo-600','Administrator',['Full system access & settings','Manage all users and settings','Set up your school profile','Complete activity visibility'],'indigo'],
                ['academic-cap','bg-emerald-600','Teacher',['Attendance marking','Grade entry & results','Lesson plans & assignments','Student communication'],'emerald'],
                ['user','bg-sky-600','Student',['View assignments & grades','Attendance history','Fee invoice access','Events & announcements'],'sky'],
                ['user-group','bg-amber-600','Parent',["Child's academic progress",'Attendance monitoring','Fee payment tracking','Direct messaging'],'amber'],
                ['briefcase','bg-violet-600','Staff',['Administrative support','Record keeping','Leave management','Day-to-day data tasks'],'violet'],
                ['calculator','bg-red-600','Accountant',['Fee management & invoicing','Payment processing','Payroll management','Financial reports'],'red'],
            ] as [$icon,$iconBg,$title,$items,$color])
            <div class="reveal group bg-white border border-zinc-200 rounded-2xl p-6 hover:border-{{ $color }}-300 hover:shadow-xl hover:shadow-{{ $color }}-500/[.08] hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-24 h-24 rounded-full bg-{{ $color }}-500/[.04] -translate-y-8 translate-x-8 group-hover:bg-{{ $color }}-500/[.08] transition-colors pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl {{ $iconBg }} flex items-center justify-center mb-5 shadow-lg shadow-{{ $color }}-600/25 group-hover:scale-110 transition-transform duration-300">
                        <flux:icon :name="$icon" class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="text-[.925rem] font-bold text-zinc-900 mb-3 tracking-tight">{{ $title }}</h3>
                    <ul class="space-y-2">
                        @foreach($items as $item)
                        <li class="flex items-center gap-2.5">
                            <span class="w-1 h-1 rounded-full bg-zinc-300 shrink-0"></span>
                            <span class="text-sm text-zinc-500">{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>



{{-- ═══════════════════════════════════════════
     TESTIMONIALS
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 overflow-hidden bg-white">
    <div class="max-w-[1200px] mx-auto">
        <div class="text-center mb-14 reveal">
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-600 mb-4 justify-center">
                <span class="block w-6 h-[1.5px] bg-indigo-600 rounded-full"></span> Testimonials
            </div>
            <h2 class="text-[clamp(2rem,4vw,3.25rem)] font-bold tracking-tight leading-tight text-zinc-900 mb-3.5">
                Loved by school <span class="shine">administrators</span>
            </h2>
            <p class="text-base text-zinc-500">See what institutions are saying about Academiq.</p>
            {{-- Rating summary --}}
            <div class="inline-flex items-center gap-3 mt-6 bg-amber-50 border border-amber-200/60 rounded-2xl px-5 py-3">
                <div class="text-amber-500 text-base tracking-widest">★★★★★</div>
                <div class="w-px h-4 bg-amber-200"></div>
                <span class="text-sm font-bold text-zinc-800">4.9 / 5</span>
                <span class="text-xs text-zinc-500">from 500+ institutions</span>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-28 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-28 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>

        <div class="animate-marquee flex gap-5" style="animation-duration:55s;">
            @foreach([
                ['AO','Adebayo Ogundimu','Principal, Greenfield Academy','bg-gradient-to-br from-indigo-500 to-indigo-700','Academiq completely transformed how we manage our 1,200-student school. The fee management alone saved us 20+ hours a week chasing payments manually.'],
                ['FK','Fatima Kainuwa','Director, Sunrise College','bg-gradient-to-br from-emerald-500 to-emerald-700','The role-based access is a game-changer. Our accountant only sees what she needs to, teachers see their classes, and parents get real-time progress.'],
                ['NB','Ngozi Badmus','Admin, Citadel Academy','bg-gradient-to-br from-amber-500 to-orange-600','The hostel management module is something we couldn\'t find anywhere else. Managing 400 boarding students used to be chaos — now it\'s a few clicks.'],
                ['EM','Emmanuel Mba','IT Lead, St. Michael\'s School','bg-gradient-to-br from-violet-500 to-violet-700','The audit trail alone gives our board complete confidence. Every action is logged and traceable — non-negotiable when handling sensitive student data.'],
                ['SA','Sola Adeyemi','Head Teacher, Brightfield Institute','bg-gradient-to-br from-orange-500 to-red-600','Exam scheduling and automatic grade calculations cut our term-end workload in half. Teachers input marks once and the system handles everything.'],
                ['AO','Adebayo Ogundimu','Principal, Greenfield Academy','bg-gradient-to-br from-indigo-500 to-indigo-700','Academiq completely transformed how we manage our 1,200-student school. The fee management alone saved us 20+ hours a week chasing payments manually.'],
                ['FK','Fatima Kainuwa','Director, Sunrise College','bg-gradient-to-br from-emerald-500 to-emerald-700','The role-based access is a game-changer. Our accountant only sees what she needs to, teachers see their classes, and parents get real-time progress.'],
                ['NB','Ngozi Badmus','Admin, Citadel Academy','bg-gradient-to-br from-amber-500 to-orange-600','The hostel management module is something we couldn\'t find anywhere else. Managing 400 boarding students used to be chaos — now it\'s a few clicks.'],
            ] as [$init,$name,$role,$bg,$quote])
            <div class="w-[340px] shrink-0 bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-zinc-300 transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-amber-500 text-sm tracking-widest">★★★★★</div>
                    <span class="text-[.6rem] font-bold text-zinc-400 bg-zinc-50 border border-zinc-200 px-2 py-1 rounded-lg">Verified</span>
                </div>
                <p class="text-sm text-zinc-600 leading-relaxed mb-5">"{{ $quote }}"</p>
                <div class="flex items-center gap-3 pt-4 border-t border-zinc-100">
                    <div class="w-10 h-10 rounded-full {{ $bg }} flex items-center justify-center text-[.65rem] font-extrabold text-white shrink-0 shadow-sm">{{ $init }}</div>
                    <div>
                        <p class="text-sm font-bold text-zinc-900">{{ $name }}</p>
                        <p class="text-[.7rem] text-zinc-400">{{ $role }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     PRICING
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-zinc-950 relative overflow-hidden" id="pricing">
    {{-- Background --}}
    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px); background-size: 52px 52px;"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] rounded-full bg-[radial-gradient(ellipse,rgba(79,70,229,.15),transparent_65%)] pointer-events-none"></div>

    <div class="relative max-w-[1100px] mx-auto">
        <div class="text-center mb-16 reveal">
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-400 mb-4 justify-center">
                <span class="block w-6 h-[1.5px] bg-indigo-400 rounded-full"></span> Pricing
            </div>
            <h2 class="text-[clamp(2rem,4vw,3.25rem)] font-bold tracking-tight leading-tight text-white mb-3.5">
                Simple, <span class="shine">transparent</span> pricing
            </h2>
            <p class="text-base text-white/50">No per-student fees. No hidden limits. Start with a 14-day free trial.</p>
        </div>

        <div class="grid grid-cols-3 max-lg:grid-cols-1 gap-5 items-stretch">

            {{-- Starter --}}
            <div class="reveal bg-white/[.04] border border-white/[.08] rounded-3xl p-8 hover:bg-white/[.06] hover:border-white/[.14] transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-[.65rem] font-extrabold tracking-widest uppercase text-white/40">Starter</p>
                    <span class="text-[.6rem] font-bold px-2.5 py-1 rounded-lg bg-white/10 text-white/50 border border-white/10">Monthly</span>
                </div>
                <div class="text-[2.75rem] font-black text-white tracking-tighter leading-none mb-1">₦25k</div>
                <p class="text-xs text-white/40 mb-6">/ month, billed monthly</p>
                <p class="text-sm text-white/50 mb-7 leading-relaxed">Perfect for small schools getting started with digital management.</p>
                <ul class="space-y-3 mb-8">
                    @foreach(['Up to 300 students','5 staff accounts','Academic & attendance','Basic fee management','Email support'] as $item)
                    <li class="flex items-center gap-3 text-sm text-white/60">
                        <span class="w-4 h-4 rounded-full bg-white/10 border border-white/15 flex items-center justify-center text-[.55rem] shrink-0 text-white/50">✓</span>{{ $item }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="flex items-center justify-center w-full py-3.5 border border-white/20 text-white/80 rounded-xl text-sm font-semibold no-underline hover:bg-white/[.08] hover:border-white/30 transition-all">
                    Start Free Trial
                </a>
            </div>

            {{-- Growth (popular) --}}
            <div class="reveal relative rounded-3xl p-px bg-gradient-to-b from-indigo-500/60 via-indigo-500/30 to-transparent shadow-2xl shadow-indigo-500/20">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[.65rem] font-extrabold px-5 py-1.5 rounded-full shadow-lg shadow-amber-500/40 tracking-wider uppercase whitespace-nowrap z-10">Most Popular</div>
                <div class="bg-zinc-900 rounded-[calc(1.5rem-1px)] p-8 h-full">
                    <div class="flex items-center justify-between mb-6">
                        <p class="text-[.65rem] font-extrabold tracking-widest uppercase text-indigo-400">Growth</p>
                        <span class="text-[.6rem] font-bold px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">Monthly</span>
                    </div>
                    <div class="text-[2.75rem] font-black text-white tracking-tighter leading-none mb-1">₦65k</div>
                    <p class="text-xs text-white/40 mb-6">/ month, billed monthly</p>
                    <p class="text-sm text-white/60 mb-7 leading-relaxed">For growing institutions that need the full feature suite.</p>
                    <ul class="space-y-3 mb-8">
                        @foreach(['Up to 1,500 students','Unlimited staff accounts','All modules including Hostel','Payroll & document generation','Two-step login & activity history','Priority support'] as $item)
                        <li class="flex items-center gap-3 text-sm text-white/80">
                            <span class="w-4 h-4 rounded-full bg-indigo-500/25 border border-indigo-500/40 flex items-center justify-center text-[.55rem] shrink-0 text-indigo-400">✓</span>{{ $item }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 w-full py-3.5 bg-indigo-600 text-white rounded-xl text-sm font-extrabold no-underline shadow-lg shadow-indigo-600/40 hover:bg-indigo-500 transition-colors">
                        Start Free Trial →
                    </a>
                </div>
            </div>

            {{-- Enterprise --}}
            <div class="reveal bg-white/[.04] border border-white/[.08] rounded-3xl p-8 hover:bg-white/[.06] hover:border-white/[.14] transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-[.65rem] font-extrabold tracking-widest uppercase text-white/40">Enterprise</p>
                    <span class="text-[.6rem] font-bold px-2.5 py-1 rounded-lg bg-white/10 text-white/50 border border-white/10">Custom</span>
                </div>
                <div class="text-[2.2rem] font-black text-white tracking-tighter leading-none mb-1">Custom</div>
                <p class="text-xs text-white/40 mb-6">contact us for pricing</p>
                <p class="text-sm text-white/50 mb-7 leading-relaxed">For large institutions, school networks, and government deployments.</p>
                <ul class="space-y-3 mb-8">
                    @foreach(['Unlimited students & staff','Multi-school management','Custom integrations & API','Dedicated account manager','Uptime guarantee & on-premise option'] as $item)
                    <li class="flex items-center gap-3 text-sm text-white/60">
                        <span class="w-4 h-4 rounded-full bg-white/10 border border-white/15 flex items-center justify-center text-[.55rem] shrink-0 text-white/50">✓</span>{{ $item }}
                    </li>
                    @endforeach
                </ul>
                <a href="#" class="flex items-center justify-center w-full py-3.5 border border-white/20 text-white/80 rounded-xl text-sm font-semibold no-underline hover:bg-white/[.08] hover:border-white/30 transition-all">
                    Contact Sales
                </a>
            </div>
        </div>

        {{-- Trust badges --}}
        <div class="mt-12 flex flex-wrap items-center justify-center gap-6 reveal">
            @foreach(['14-day free trial', 'No credit card required', 'Cancel anytime', 'Data export included'] as $badge)
            <div class="flex items-center gap-2 text-xs text-white/40">
                <span class="w-4 h-4 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-500 text-[.55rem]">✓</span>
                {{ $badge }}
            </div>
            @endforeach
        </div>
    </div>
</section>



{{-- ═══════════════════════════════════════════
     FAQ
═══════════════════════════════════════════ --}}
<section class="py-28 px-6 bg-white" id="faq">
    <div class="max-w-[720px] mx-auto">
        <div class="text-center mb-14 reveal">
            <div class="inline-flex items-center gap-2.5 text-[.7rem] font-extrabold tracking-[.12em] uppercase text-indigo-600 mb-4 justify-center">
                <span class="block w-6 h-[1.5px] bg-indigo-600 rounded-full"></span> FAQ
            </div>
            <h2 class="text-[clamp(2rem,4vw,3.25rem)] font-bold tracking-tight leading-tight text-zinc-900 mb-3.5">
                Frequently asked <span class="shine">questions</span>
            </h2>
            <p class="text-base text-zinc-500">Have a different question? <a href="#" class="text-indigo-600 font-semibold hover:underline">Reach out to our team.</a></p>
        </div>

        <div class="reveal space-y-3">
            <flux:accordion transition exclusive>
                <flux:accordion.item heading="What is Academiq?">
                    <p class="text-sm text-zinc-500 leading-relaxed">Academiq is a full-featured Educational Management System for schools, colleges, and educational institutions. It manages everything from admissions to exams, attendance to payroll — all in one platform.</p>
                </flux:accordion.item>

                <flux:accordion.item heading="Is my school's data kept separate from others?">
                    <p class="text-sm text-zinc-500 leading-relaxed">Absolutely. Every school on Academiq operates in its own completely private space. Your students, staff, and financial data are never accessible to anyone outside your school.</p>
                </flux:accordion.item>

                <flux:accordion.item heading="Can parents access their child's information?">
                    <p class="text-sm text-zinc-500 leading-relaxed">Yes. Parents have a dedicated login with access limited to their own child's attendance, grades, fee invoices, and messages. They cannot see any other student's data.</p>
                </flux:accordion.item>

                <flux:accordion.item heading="Does Academiq support boarding/hostel schools?">
                    <p class="text-sm text-zinc-500 leading-relaxed">Yes — Academiq includes a dedicated Hostel Management module covering buildings, rooms, student allocations, visitor tracking, and warden assignments.</p>
                </flux:accordion.item>

                <flux:accordion.item heading="How does the exam and grading system work?">
                    <p class="text-sm text-zinc-500 leading-relaxed">You create exams, schedule them, then teachers enter marks. The system automatically calculates grades based on your configured grade scale and publishes results.</p>
                </flux:accordion.item>

                <flux:accordion.item heading="Can I migrate from our existing system?">
                    <p class="text-sm text-zinc-500 leading-relaxed">Yes. Our onboarding team can help you import existing student records, staff profiles, and historical data. We support CSV imports and offer guided migration for enterprise customers.</p>
                </flux:accordion.item>
            </flux:accordion>
        </div>

        {{-- CTA nudge --}}
        <div class="reveal mt-12 bg-indigo-50 border border-indigo-100 rounded-2xl p-6 flex items-center justify-between gap-4 max-sm:flex-col max-sm:text-center">
            <div>
                <p class="text-sm font-bold text-zinc-900 mb-1">Still have questions?</p>
                <p class="text-xs text-zinc-500">Our team typically responds within 2 hours.</p>
            </div>
            <a href="#" class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-xs font-extrabold rounded-xl no-underline hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/25">
                Chat with us →
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA
═══════════════════════════════════════════ --}}
<section class="relative py-32 px-6 overflow-hidden bg-zinc-900">
    {{-- Grid overlay --}}
    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 48px 48px;"></div>

    {{-- Glow orbs --}}
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[900px] h-[700px] rounded-full bg-[radial-gradient(ellipse,rgba(79,70,229,.25),transparent_65%)] pointer-events-none"></div>
    <div class="absolute -bottom-20 right-[5%] w-[500px] h-[500px] rounded-full bg-[radial-gradient(circle,rgba(16,185,129,.12),transparent_65%)] pointer-events-none"></div>
    <div class="absolute -bottom-20 left-[5%] w-[400px] h-[400px] rounded-full bg-[radial-gradient(circle,rgba(139,92,246,.08),transparent_65%)] pointer-events-none"></div>

    <div class="relative z-10 max-w-[720px] mx-auto text-center">
        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/[.06] border border-white/[.12] text-[.7rem] font-bold text-white/60 uppercase tracking-wider mb-8">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 pulse-beacon"></span>
            500+ institutions already onboard
        </div>

        <h2 class="text-[clamp(2.5rem,5.5vw,4.5rem)] font-bold tracking-tighter text-white leading-[1.04] mb-6">
            Ready to <span class="italic font-light">transform</span><br>your institution?
        </h2>
        <p class="text-lg text-white/50 mb-10 leading-relaxed max-w-[520px] mx-auto">Join hundreds of schools already running on Academiq. Setup takes less than 10 minutes.</p>

        <div class="flex flex-wrap gap-4 justify-center items-center mb-8">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2.5 px-8 py-4 bg-white text-indigo-600 text-[.925rem] font-extrabold rounded-2xl no-underline shadow-2xl shadow-white/10 hover:-translate-y-0.5 hover:shadow-white/20 transition-all">
                Start Your Free Trial
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="#" class="inline-flex items-center gap-2 px-8 py-4 text-white/70 border border-white/15 text-[.925rem] font-medium rounded-2xl no-underline hover:bg-white/[.06] hover:border-white/25 hover:text-white/90 transition-all">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Schedule a Demo
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-5">
            @foreach(['14-day free trial', 'No credit card', 'Cancel anytime'] as $item)
            <span class="flex items-center gap-1.5 text-xs text-white/35">
                <span class="w-3.5 h-3.5 rounded-full bg-white/10 flex items-center justify-center text-white/40 text-[.5rem]">✓</span>
                {{ $item }}
            </span>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ --}}
<footer class="border-t border-zinc-100 pt-16 pb-10 px-6 bg-white">
    <div class="max-w-[1200px] mx-auto">
        <div class="grid grid-cols-[2fr_1fr_1fr_1fr_1fr] max-lg:grid-cols-2 max-sm:grid-cols-1 gap-12 mb-14">
            <div>
                <a href="/" class="flex items-center gap-2.5 no-underline mb-5">
                    <x-app-logo :sidebar="false" href="{{ route('home') }}" wire:navigate />
                </a>
                <p class="text-sm text-zinc-400 leading-relaxed max-w-[240px] my-6">The modern standard for educational management. Built for schools that want to run smarter, not harder.</p>
                {{-- Social links --}}
                <div class="flex gap-3">
                    @foreach([
                        ['Twitter', 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                        ['LinkedIn', 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
                    ] as [$name, $path])
                    <a href="#" aria-label="{{ $name }}" class="w-8 h-8 rounded-lg bg-zinc-100 border border-zinc-200 flex items-center justify-center text-zinc-400 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all no-underline">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @foreach([['Product',['Features','Pricing',"What's New",'Roadmap']],['Modules',['Students','Exams','Fees','Hostel']],['Company',['About','Blog','Careers','Contact']],['Legal',['Terms','Privacy','Security','Data Protection']]] as [$heading,$links])
            <div>
                <h4 class="text-[.65rem] font-extrabold tracking-widest uppercase text-zinc-900 mb-5">{{ $heading }}</h4>
                <ul class="space-y-3">
                    @foreach($links as $link)
                    <li><a href="#" class="text-sm text-zinc-400 no-underline hover:text-zinc-900 transition-colors">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-zinc-100 pt-7 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <p class="text-xs text-zinc-400">&copy; {{ date('Y') }} Academiq. All rights reserved.</p>
                <span class="hidden sm:flex items-center gap-1.5 text-xs text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-lg font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> All systems operational
                </span>
            </div>
            <div class="flex items-center gap-1 text-xs text-zinc-400">
                Made with
                <svg class="w-3.5 h-3.5 text-red-500 mx-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                for schools everywhere
            </div>
        </div>
    </div>
</footer>

{{-- ═══════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════ --}}
<script>
    // Reveal on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 65);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.07 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
@fluxScripts
</body>
</html>