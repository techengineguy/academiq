<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @include('partials.head')
    <style>
        .font-serif-display { font-family: 'Work Sans', sans-serif; }
        @keyframes marquee { from { transform: translateX(0) } to { transform: translateX(-50%) } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(24px) } to { opacity: 1; transform: translateY(0) } }
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-12px) } to { opacity: 1; transform: translateY(0) } }
        @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.4)} }
        .animate-marquee { animation: marquee 30s linear infinite; width: max-content; }
        .animate-fade-up { animation: fadeUp .7s ease both; }
        .animate-fade-down { animation: fadeDown .6s ease both; }
        .pulse-dot { animation: pulse-dot 2s infinite; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .grid-bg {
            background-image: linear-gradient(rgba(99,102,241,.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(99,102,241,.05) 1px, transparent 1px);
            background-size: 64px 64px;
        }
        .hero-glow { background: radial-gradient(ellipse 80% 50% at 50% 0%, rgba(99,102,241,.10) 0%, transparent 70%); }
        .card-hover { transition: box-shadow .25s, border-color .25s, transform .25s; }
        .card-hover:hover { box-shadow: 0 12px 40px rgba(99,102,241,.12); border-color: rgba(99,102,241,.35) !important; transform: translateY(-2px); }
        .gradient-heading { background: linear-gradient(135deg, #4338ca 0%, #6366f1 60%, #818cf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
</head>
<body class="bg-white text-zinc-900 antialiased overflow-x-hidden">

{{-- ── Nav ── --}}
<header id="nav" class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <div class="mx-auto max-w-7xl px-6 h-16 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black shadow-md shadow-indigo-600/20">Aq</div>
            <span class="text-base font-semibold text-zinc-900">Academiq</span>
        </a>
        <nav class="hidden md:flex items-center gap-1">
            @foreach(['#features' => 'Features', '#how-it-works' => 'How It Works', '#pricing' => 'Pricing', '#faq' => 'FAQ'] as $href => $label)
            <a href="{{ $href }}" class="px-4 py-2 text-sm text-zinc-500 hover:text-zinc-900 rounded-lg hover:bg-zinc-100 transition-all font-medium">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="hidden sm:block text-sm text-zinc-500 hover:text-zinc-900 transition-colors px-3 py-2 font-medium">Sign in</a>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-md shadow-indigo-600/20">
                Get started free
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</header>

{{-- ── Hero ── --}}
<section class="relative pt-28 pb-0 overflow-hidden">
    <div class="absolute inset-0 grid-bg pointer-events-none"></div>
    <div class="absolute inset-0 hero-glow pointer-events-none"></div>

    <div class="relative z-10 max-w-5xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-indigo-50 border border-indigo-100 text-sm text-indigo-700 font-semibold mb-8 animate-fade-down">
            <span class="relative flex h-2 w-2">
                <span class="pulse-dot absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            Multi-Tenant SaaS Architecture — now available
        </div>

        <h1 class="text-5xl md:text-[4.5rem] font-light leading-[1.08] tracking-tight text-zinc-900 mb-6 animate-fade-up">
            The complete platform for<br>
            <span class="font-serif-display italic gradient-heading">managing your school.</span>
        </h1>

        <p class="text-xl text-zinc-500 max-w-2xl mx-auto mb-10 leading-relaxed animate-fade-up" style="animation-delay:.1s">
            Academiq handles everything from admissions to exams, fee collection to payroll — so your institution runs on one powerful, connected platform.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center mb-5 animate-fade-up" style="animation-delay:.2s">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-xl shadow-indigo-600/25 hover:-translate-y-0.5">
                Get started for free
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="#features" class="inline-flex items-center gap-2 px-8 py-4 text-base font-medium text-zinc-600 hover:text-zinc-900 bg-white border border-zinc-200 hover:border-zinc-300 rounded-xl transition-all shadow-sm">
                See all features
            </a>
        </div>
        <p class="text-sm text-zinc-400 animate-fade-up" style="animation-delay:.25s">No credit card required. 14-day free trial.</p>

        <div class="flex items-center justify-center gap-3 mt-6 mb-16 animate-fade-up" style="animation-delay:.3s">
            <div class="flex -space-x-2">
                @foreach(['bg-indigo-600','bg-emerald-500','bg-orange-500','bg-violet-500'] as $i => $bg)
                <div class="w-8 h-8 rounded-full {{ $bg }} border-2 border-white flex items-center justify-center text-white text-xs font-bold shadow-sm">{{ ['AO','FK','NB','EM'][$i] }}</div>
                @endforeach
            </div>
            <p class="text-sm text-zinc-500"><span class="font-semibold text-zinc-900">500+</span> institutions across West Africa</p>
        </div>
    </div>

    {{-- App Preview --}}
    <div class="relative z-10 max-w-6xl mx-auto px-6 animate-fade-up" style="animation-delay:.4s">
        <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden shadow-2xl shadow-zinc-900/10">
            <div class="flex items-center gap-3 px-5 py-3.5 bg-zinc-50 border-b border-zinc-200">
                <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-400"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                    <span class="w-3 h-3 rounded-full bg-green-400"></span>
                </div>
                <div class="flex-1 flex justify-center">
                    <div class="bg-white border border-zinc-200 rounded-lg px-4 py-1.5 text-xs text-zinc-400 max-w-xs w-full text-center shadow-sm">app.academiq.io/dashboard</div>
                </div>
            </div>
            <div class="grid grid-cols-[200px_1fr] min-h-[420px]">
                <div class="hidden md:block bg-zinc-900 border-r border-zinc-800 py-5">
                    <div class="px-4 pb-4 mb-3 border-b border-zinc-800">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black">Aq</div>
                            <span class="text-sm font-semibold text-white">Academiq</span>
                        </div>
                    </div>
                    <div class="space-y-0.5 px-3">
                        @foreach([['Dashboard',true],['Students',false],['Academic',false],['Staff',false],['Exams',false],['Fees',false],['Attendance',false],['Hostel',false]] as [$label,$active])
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium {{ $active ? 'bg-indigo-600 text-white' : 'text-zinc-500 hover:bg-zinc-800 hover:text-zinc-300 cursor-pointer' }} transition-all">
                            <span class="w-1.5 h-1.5 rounded-full bg-current {{ $active ? 'opacity-100' : 'opacity-40' }}"></span>
                            {{ $label }}
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="p-6 bg-zinc-50">
                    <div class="mb-5">
                        <p class="text-base font-semibold text-zinc-900">Good morning, Administrator 👋</p>
                        <p class="text-xs text-zinc-400 mt-0.5">{{ now()->format('l, F j, Y') }} — Term 2 in progress</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        @foreach([['1,284','Total Students','↑ 12%','emerald'],['86','Teaching Staff','↑ 3%','emerald'],['₦4.2M','Fee Collection','↑ 8%','emerald'],['137','Pending Invoices','↑ 5','red']] as [$val,$lbl,$chg,$col])
                        <div class="bg-white border border-zinc-200 rounded-xl p-3.5 shadow-sm">
                            <p class="text-xl font-bold text-zinc-900">{{ $val }}</p>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ $lbl }}</p>
                            <span class="inline-block mt-1.5 text-xs font-bold px-2 py-0.5 rounded-full {{ $col === 'emerald' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">{{ $chg }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="space-y-2">
                        @foreach([['AO','Adaeze Okafor','JSS 3A · AQ/2024/001','Active','indigo'],['EN','Emeka Nwosu','SS 2B · AQ/2024/002','Active','emerald'],['FB','Fatima Bello','JSS 1C · AQ/2024/003','Pending','amber']] as [$init,$name,$meta,$status,$col])
                        <div class="flex items-center gap-3 bg-white border border-zinc-200 rounded-xl px-4 py-3 shadow-sm">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0 {{ $col === 'indigo' ? 'bg-indigo-600' : ($col === 'emerald' ? 'bg-emerald-500' : 'bg-amber-500') }}">{{ $init }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-zinc-900">{{ $name }}</p>
                                <p class="text-xs text-zinc-400">{{ $meta }}</p>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $status === 'Active' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">{{ $status }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
    </div>
</section>

{{-- ── Logos ── --}}
<div class="py-14 border-y border-zinc-100 overflow-hidden bg-zinc-50/70">
    <p class="text-center text-xs font-bold text-zinc-400 uppercase tracking-widest mb-8">Trusted by modern educational institutions</p>
    <div class="overflow-hidden">
        <div class="animate-marquee flex gap-16 items-center">
            @foreach(array_merge(['Greenfield Academy','Lagos International School','Citadel Academy','Sunrise College','Hillcrest Secondary','St. Michael\'s School','Brightfield Institute','Apex Academy'],['Greenfield Academy','Lagos International School','Citadel Academy','Sunrise College','Hillcrest Secondary','St. Michael\'s School','Brightfield Institute','Apex Academy']) as $name)
            <span class="text-sm font-bold text-zinc-300 whitespace-nowrap uppercase tracking-wider">{{ $name }}</span>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Features ── --}}
<section class="py-28 px-6" id="features">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> Features
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-5">
                Everything your school <span class="font-serif-display italic gradient-heading">needs</span>,<br class="hidden md:block"> nothing it doesn't
            </h2>
            <p class="text-lg text-zinc-500 max-w-xl mx-auto">From the first admission to the final exam result — Academiq manages every step of your institution's workflow.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach([
                ['🎓','Student Management','bg-indigo-50 text-indigo-600','Complete student profiles with admission tracking, parent relationships, medical records, and class promotion history.'],
                ['📋','Exam & Grading','bg-emerald-50 text-emerald-600','Create exams, schedule timetables, record results and auto-calculate grades with configurable grade scales.'],
                ['💰','Fee Management','bg-amber-50 text-amber-600','Generate invoices, track payments, manage fee structures per class, handle partial payments and produce reports.'],
                ['✅','Attendance Tracking','bg-rose-50 text-rose-600','Daily student and staff attendance with absence notifications, reports, and permission-based access.'],
                ['🏢','Hostel Management','bg-violet-50 text-violet-600','Manage dormitory buildings, rooms, student allocations, visitor tracking, and warden assignments.'],
                ['💬','Communications Hub','bg-sky-50 text-sky-600','Broadcast announcements, manage events, send internal messages, and push notifications institution-wide.'],
                ['📄','Document Generation','bg-amber-50 text-amber-600','Generate certificates, ID cards, and custom document templates — print-ready and beautifully formatted.'],
                ['💼','Staff & Payroll','bg-indigo-50 text-indigo-600','Teacher profiles, monthly payroll processing, allowances, deductions, tax calculation, and payment tracking.'],
                ['🔐','Role-Based Access','bg-rose-50 text-rose-600','6 granular roles — Admin, Teacher, Student, Parent, Staff, Accountant — each with scoped permissions.'],
            ] as [$icon,$title,$iconClass,$desc])
            <div class="bg-white border border-zinc-200 rounded-2xl p-7 card-hover cursor-default">
                <div class="w-11 h-11 rounded-xl {{ $iconClass }} flex items-center justify-center text-xl mb-5">{{ $icon }}</div>
                <h3 class="text-base font-bold text-zinc-900 mb-2.5">{{ $title }}</h3>
                <p class="text-sm text-zinc-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── How It Works ── --}}
<section class="py-28 px-6 bg-zinc-50 border-y border-zinc-100" id="how-it-works">
    <div class="max-w-7xl mx-auto">
        <div class="mb-16 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> How It Works
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-5">
                Up and running <span class="font-serif-display italic gradient-heading">in minutes</span>
            </h2>
            <p class="text-lg text-zinc-500 max-w-md">No complex onboarding. No lengthy setup. Start managing your institution right away.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([['01','Create Your Institution','Sign up and enter your institution details. Your isolated workspace is provisioned instantly with complete data separation.'],['02','Configure & Customize','Set up academic years, classes, subjects, fee structures, and grade scales. Upload your logo and configure your profile.'],['03','Invite Your Team','Add teachers, staff, and accountants with appropriate role-based permissions. Onboard students and parents with ease.']] as [$num,$title,$desc])
            <div class="reveal bg-white border border-zinc-200 rounded-2xl p-8 relative overflow-hidden card-hover">
                <div class="absolute top-3 right-5 text-[80px] font-black text-zinc-100 leading-none select-none">{{ $num }}</div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-base mb-6 shadow-lg shadow-indigo-600/20">{{ $num }}</div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-3">{{ $title }}</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Split: Multi-Tenant ── --}}
<section class="py-28 px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center reveal">
        <div>
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> Multi-Tenancy
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-6">
                One platform.<br><span class="font-serif-display italic gradient-heading">Every institution,</span><br>completely isolated.
            </h2>
            <p class="text-zinc-500 leading-relaxed mb-8 text-lg">Built as a true multi-institutional SaaS — every school operates independently with complete data isolation at the database level.</p>
            <ul class="space-y-4">
                @foreach(['Each institution has its own students, staff, finances, and settings','Institution ID foreign keys enforce strict data boundaries across all 61 tables','Switch between institutions instantly from a single admin login','Enterprise-grade tenant isolation — nothing leaks between institutions'] as $item)
                <li class="flex items-start gap-3 text-zinc-600 text-sm">
                    <span class="mt-0.5 w-5 h-5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xs font-bold border border-emerald-100">✓</span>
                    {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl p-6 shadow-xl shadow-zinc-900/8">
            <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-5">Institution Switcher</p>
            <div class="space-y-3">
                <div class="flex items-center gap-3 bg-indigo-600 rounded-xl px-4 py-3.5 shadow-lg shadow-indigo-600/20">
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white text-xs font-black flex-shrink-0">GA</div>
                    <div class="flex-1"><p class="text-sm font-bold text-white">Greenfield Academy</p><p class="text-xs text-indigo-200">1,284 students · 86 staff</p></div>
                    <span class="text-xs font-bold bg-white/20 text-white px-2.5 py-1 rounded-full">Active</span>
                </div>
                @foreach([['SC','Sunrise College','642 students · 41 staff','emerald'],['CA','Citadel Academy','890 students · 63 staff','rose']] as [$init,$name,$meta,$col])
                <div class="flex items-center gap-3 border border-zinc-200 rounded-xl px-4 py-3.5 hover:border-indigo-200 hover:bg-indigo-50/30 cursor-pointer transition-all">
                    <div class="w-9 h-9 rounded-lg {{ $col === 'emerald' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500' }} flex items-center justify-center text-xs font-black flex-shrink-0">{{ $init }}</div>
                    <div><p class="text-sm font-bold text-zinc-900">{{ $name }}</p><p class="text-xs text-zinc-400">{{ $meta }}</p></div>
                </div>
                @endforeach
                <div class="flex items-center justify-center gap-2 border border-dashed border-zinc-300 rounded-xl px-4 py-3.5 text-sm text-zinc-400 cursor-pointer hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/30 transition-all">
                    <span class="text-lg leading-none">+</span> Add New Institution
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Split: Security ── --}}
<section class="py-28 px-6 bg-zinc-50 border-y border-zinc-100">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center reveal">
        <div class="order-2 lg:order-1 bg-white border border-zinc-200 rounded-2xl p-6 shadow-xl shadow-zinc-900/8">
            <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-5">Activity Log</p>
            <div class="space-y-2">
                @foreach([['LOGIN','Admin logged in from 197.210.x.x','2 min ago','emerald'],['CREATE','Student Adaeze Okafor admitted','14 min ago','indigo'],['UPDATE','Fee structure updated for SS1','1 hr ago','amber'],['DELETE','Draft invoice #INV-0821 discarded','3 hrs ago','red'],['EXPORT','Term 1 results PDF exported','5 hrs ago','violet']] as [$action,$text,$time,$col])
                <div class="flex items-center gap-3 bg-zinc-50 border border-zinc-100 rounded-xl px-4 py-3">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0 border
                        {{ $col === 'emerald' ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                         : ($col === 'indigo'  ? 'bg-indigo-50 text-indigo-600 border-indigo-100'
                         : ($col === 'amber'   ? 'bg-amber-50 text-amber-600 border-amber-100'
                         : ($col === 'violet'  ? 'bg-violet-50 text-violet-600 border-violet-100'
                         : 'bg-red-50 text-red-500 border-red-100'))) }}">{{ $action }}</span>
                    <span class="text-xs text-zinc-600 flex-1">{{ $text }}</span>
                    <span class="text-xs text-zinc-400 whitespace-nowrap">{{ $time }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="order-1 lg:order-2">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> Security & Audit
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-6">
                Every action.<br><span class="font-serif-display italic gradient-heading">Fully logged</span><br>and traceable.
            </h2>
            <p class="text-zinc-500 leading-relaxed mb-8 text-lg">Enterprise-grade security with complete audit trails, two-factor authentication, and role-based access that keeps sensitive data protected.</p>
            <ul class="space-y-4">
                @foreach(['TOTP-based two-factor authentication for all user roles','Complete activity audit logs — who did what, and when','Soft deletes preserve data history across all major entities','CSRF, XSS, and SQL injection protection out of the box'] as $item)
                <li class="flex items-start gap-3 text-zinc-600 text-sm">
                    <span class="mt-0.5 w-5 h-5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xs font-bold border border-emerald-100">✓</span>
                    {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- ── Comparison ── --}}
<section class="py-28 px-6">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4 justify-center">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> Comparison
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-4">See how we <span class="font-serif-display italic gradient-heading">stack up</span></h2>
            <p class="text-zinc-500">A transparent look at what matters most for modern school management.</p>
        </div>
        <div class="reveal overflow-hidden rounded-2xl border border-zinc-200 shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-200">
                        <th class="text-left p-5 text-sm font-semibold text-zinc-500 bg-zinc-50">Feature</th>
                        <th class="p-5 text-sm font-bold text-white bg-indigo-600 text-center">Academiq</th>
                        <th class="p-5 text-sm font-semibold text-zinc-500 bg-zinc-50 text-center">SchoolEdge</th>
                        <th class="p-5 text-sm font-semibold text-zinc-500 bg-zinc-50 text-center">ClassDojo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Multi-tenant SaaS Architecture',true,false,false],
                        ['Two-Factor Authentication (2FA)',true,true,false],
                        ['Complete Fee & Payroll Management',true,true,false],
                        ['Hostel Management Module',true,false,false],
                        ['Full Audit Trail & Activity Logs',true,false,false],
                        ['Granular Role-Based Access (6 Roles)',true,'3 Roles','2 Roles'],
                        ['Document & Certificate Generation',true,false,false],
                        ['Offline-First & Soft Deletes',true,false,false],
                    ] as [$feature,$ours,$b,$c])
                    <tr class="border-b border-zinc-100 last:border-0 hover:bg-zinc-50/50 transition-colors">
                        <td class="p-5 text-sm text-zinc-700 font-medium">{{ $feature }}</td>
                        <td class="p-5 text-center bg-indigo-50/40 border-x border-indigo-100">
                            @if($ours === true)<span class="text-emerald-500 text-lg font-bold">✓</span>@else<span class="text-sm font-semibold text-indigo-600">{{ $ours }}</span>@endif
                        </td>
                        <td class="p-5 text-center">
                            @if($b === true)<span class="text-emerald-500 text-lg font-bold">✓</span>@elseif($b === false)<span class="text-zinc-300 text-lg">✗</span>@else<span class="text-sm text-zinc-400">{{ $b }}</span>@endif
                        </td>
                        <td class="p-5 text-center">
                            @if($c === true)<span class="text-emerald-500 text-lg font-bold">✓</span>@elseif($c === false)<span class="text-zinc-300 text-lg">✗</span>@else<span class="text-sm text-zinc-400">{{ $c }}</span>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ── User Roles ── --}}
<section class="py-28 px-6 bg-zinc-50 border-y border-zinc-100">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4 justify-center">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> User Roles
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-4">
                Built for <span class="font-serif-display italic gradient-heading">everyone</span><br>in your institution
            </h2>
            <p class="text-zinc-500 max-w-xl mx-auto">Every stakeholder gets a tailored experience with exactly the right permissions.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach([
                ['🛡️','Administrator','bg-indigo-50',['Full system access & settings','User management & roles','Institution configuration','Complete audit visibility']],
                ['📚','Teacher','bg-emerald-50',['Attendance marking','Grade entry & results','Lesson plans & assignments','Student communication']],
                ['🎒','Student','bg-sky-50',['View assignments & grades','Attendance history','Fee invoice access','Events & announcements']],
                ['👨‍👩‍👧','Parent','bg-amber-50',['Child\'s academic progress','Attendance monitoring','Fee payment tracking','Direct messaging']],
                ['💼','Staff','bg-violet-50',['Administrative support','Record keeping','Leave management','Data entry workflows']],
                ['🧾','Accountant','bg-rose-50',['Fee management & invoicing','Payment processing','Payroll management','Financial reports']],
            ] as [$icon,$title,$bg,$items])
            <div class="reveal bg-white border border-zinc-200 rounded-2xl p-7 card-hover">
                <div class="w-12 h-12 rounded-2xl {{ $bg }} flex items-center justify-center text-2xl mb-5">{{ $icon }}</div>
                <h3 class="text-base font-bold text-zinc-900 mb-4">{{ $title }}</h3>
                <ul class="space-y-2.5">
                    @foreach($items as $item)
                    <li class="flex items-center gap-2.5 text-sm text-zinc-500"><span class="text-indigo-500 font-bold text-xs">→</span> {{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Testimonials ── --}}
<section class="py-28 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4 justify-center">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> Testimonials
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-4">
                Loved by school <span class="font-serif-display italic gradient-heading">administrators</span>
            </h2>
            <p class="text-zinc-500">See what institutions are saying about Academiq.</p>
        </div>
        <div class="relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
            <div class="animate-marquee flex gap-5" style="animation-duration:50s">
                @foreach([
                    ['AO','Adebayo Ogundimu','Principal, Greenfield Academy','indigo','Academiq completely transformed how we manage our 1,200-student school. The fee management alone saved us 20+ hours a week chasing payments manually.'],
                    ['FK','Fatima Kainuwa','Director, Sunrise College','emerald','The role-based access is a game-changer. Our accountant only sees what she needs to, teachers see their classes, and parents get real-time progress updates.'],
                    ['NB','Ngozi Badmus','Admin, Citadel Academy','orange','The hostel management module is something we couldn\'t find anywhere else. Managing 400 boarding students used to be chaos — now it\'s just a few clicks.'],
                    ['EM','Emmanuel Mba','IT Lead, St. Michael\'s School','violet','The audit trail alone gives our board complete confidence. Every action is logged and traceable. For a school handling sensitive student data, that\'s non-negotiable.'],
                    ['SA','Sola Adeyemi','Head Teacher, Brightfield Institute','amber','Exam scheduling and automatic grade calculations cut our term-end workload in half. Teachers input marks once and the system handles everything else.'],
                    ['AO','Adebayo Ogundimu','Principal, Greenfield Academy','indigo','Academiq completely transformed how we manage our 1,200-student school. The fee management alone saved us 20+ hours a week chasing payments manually.'],
                    ['FK','Fatima Kainuwa','Director, Sunrise College','emerald','The role-based access is a game-changer. Our accountant only sees what she needs to, teachers see their classes, and parents get real-time progress updates.'],
                    ['NB','Ngozi Badmus','Admin, Citadel Academy','orange','The hostel management module is something we couldn\'t find anywhere else. Managing 400 boarding students used to be chaos — now it\'s just a few clicks.'],
                ] as [$init,$name,$role,$col,$quote])
                <div class="w-80 flex-shrink-0 bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm">
                    <div class="text-amber-400 text-sm mb-3">★★★★★</div>
                    <p class="text-sm text-zinc-500 leading-relaxed mb-5">"{{ $quote }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0
                            {{ $col === 'indigo' ? 'bg-indigo-600' : ($col === 'emerald' ? 'bg-emerald-500' : ($col === 'orange' ? 'bg-orange-500' : ($col === 'violet' ? 'bg-violet-600' : 'bg-amber-500'))) }}">{{ $init }}</div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900">{{ $name }}</p>
                            <p class="text-xs text-zinc-400">{{ $role }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── Pricing ── --}}
<section class="py-28 px-6 bg-zinc-50 border-y border-zinc-100" id="pricing">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4 justify-center">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> Pricing
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-4">
                Simple, <span class="font-serif-display italic gradient-heading">transparent</span> pricing
            </h2>
            <p class="text-zinc-500">No per-student fees. No hidden limits. Start with a 14-day free trial.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start max-w-5xl mx-auto">
            <div class="reveal bg-white border border-zinc-200 rounded-2xl p-8 shadow-sm">
                <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-4">Starter</p>
                <div class="text-5xl font-black text-zinc-900 tracking-tight mb-1">₦25k</div>
                <p class="text-sm text-zinc-400 mb-6">/ month, billed monthly</p>
                <p class="text-sm text-zinc-500 mb-8 leading-relaxed">Perfect for small schools getting started with digital management.</p>
                <ul class="space-y-3 mb-8">
                    @foreach(['Up to 300 students','5 staff accounts','Academic & attendance modules','Basic fee management','Email support'] as $item)
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <span class="w-4 h-4 rounded-full bg-zinc-100 text-zinc-400 flex items-center justify-center text-xs flex-shrink-0">✓</span> {{ $item }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block w-full text-center py-3 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">Start Free Trial</a>
            </div>
            <div class="reveal relative bg-indigo-600 rounded-2xl p-8 shadow-2xl shadow-indigo-600/25 scale-[1.03]">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-md whitespace-nowrap">Most Popular</div>
                <p class="text-xs font-bold text-indigo-200 uppercase tracking-widest mb-4">Growth</p>
                <div class="text-5xl font-black text-white tracking-tight mb-1">₦65k</div>
                <p class="text-sm text-indigo-200 mb-6">/ month, billed monthly</p>
                <p class="text-sm text-indigo-100 mb-8 leading-relaxed">For growing institutions that need the full feature suite.</p>
                <ul class="space-y-3 mb-8">
                    @foreach(['Up to 1,500 students','Unlimited staff accounts','All modules including Hostel','Payroll & document generation','2FA & audit trails','Priority support'] as $item)
                    <li class="flex items-center gap-3 text-sm text-white">
                        <span class="w-4 h-4 rounded-full bg-white/20 flex items-center justify-center text-xs flex-shrink-0">✓</span> {{ $item }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block w-full text-center py-3 rounded-xl bg-white text-indigo-600 text-sm font-bold hover:bg-indigo-50 transition-all shadow-md">Start Free Trial</a>
            </div>
            <div class="reveal bg-white border border-zinc-200 rounded-2xl p-8 shadow-sm">
                <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-4">Enterprise</p>
                <div class="text-4xl font-black text-zinc-900 tracking-tight mb-1">Custom</div>
                <p class="text-sm text-zinc-400 mb-6">contact us for pricing</p>
                <p class="text-sm text-zinc-500 mb-8 leading-relaxed">For large institutions, school networks, and government deployments.</p>
                <ul class="space-y-3 mb-8">
                    @foreach(['Unlimited students & staff','Multi-institution management','Custom integrations & API','Dedicated account manager','SLA & on-premise option'] as $item)
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <span class="w-4 h-4 rounded-full bg-zinc-100 text-zinc-400 flex items-center justify-center text-xs flex-shrink-0">✓</span> {{ $item }}
                    </li>
                    @endforeach
                </ul>
                <a href="#" class="block w-full text-center py-3 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">Contact Sales</a>
            </div>
        </div>
    </div>
</section>

{{-- ── FAQ ── --}}
<section class="py-28 px-6" id="faq">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4 justify-center">
                <span class="w-5 h-px bg-indigo-600 rounded"></span> FAQ
            </p>
            <h2 class="text-4xl md:text-5xl font-light tracking-tight text-zinc-900 mb-4">
                Frequently asked <span class="font-serif-display italic gradient-heading">questions</span>
            </h2>
            <p class="text-zinc-500">Have a different question? <a href="#" class="text-indigo-600 hover:underline">Reach out to our team.</a></p>
        </div>
        <div class="space-y-3">
            @foreach([
                ['What is Academiq?','Academiq is a full-featured Educational Management System built for schools, colleges, and educational institutions. It manages everything from admissions to exams, attendance to payroll — all in one platform.'],
                ['Is my institution\'s data kept separate from others?','Absolutely. Academiq is built as a multi-tenant SaaS platform. Every institution has completely isolated data at the database level — enforced through institution ID constraints across all 61 tables.'],
                ['Can parents access their child\'s information?','Yes. Parents have a dedicated role with scoped access to their child\'s attendance, grades, fee invoices, and messages. They cannot see other students\' data.'],
                ['Does Academiq support boarding/hostel schools?','Yes — Academiq includes a dedicated Hostel Management module covering buildings, rooms, student allocations, visitor tracking, and warden assignments. It\'s one of our most unique features.'],
                ['How does the exam and grading system work?','You create exams (mid-term, final, unit tests), schedule them, then teachers enter marks. The system automatically calculates grades based on your configured grade scale and publishes results.'],
                ['Can I migrate from our existing system?','Yes. Our onboarding team can help you import existing student records, staff profiles, and historical data. We support CSV imports and offer a guided migration service for enterprise customers.'],
            ] as [$q,$a])
            <div class="reveal faq-item bg-white border border-zinc-200 rounded-2xl p-6 cursor-pointer hover:border-indigo-200 hover:shadow-sm transition-all">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="text-base font-semibold text-zinc-900">{{ $q }}</h3>
                    <span class="faq-toggle text-indigo-500 text-xl flex-shrink-0 font-light">+</span>
                </div>
                <div class="faq-answer hidden mt-4 text-sm text-zinc-500 leading-relaxed">{{ $a }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="relative py-28 px-6 overflow-hidden bg-indigo-600">
    <div class="absolute inset-0 grid-bg opacity-20 pointer-events-none"></div>
    <div class="absolute -top-32 left-1/3 w-[600px] h-[600px] bg-white/8 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-32 right-1/3 w-[400px] h-[400px] bg-emerald-400/15 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="relative z-10 max-w-3xl mx-auto text-center">
        <h2 class="text-4xl md:text-6xl font-light tracking-tight text-white mb-6 leading-tight">
            Ready to <span class="font-serif-display italic">transform</span><br>your institution?
        </h2>
        <p class="text-lg text-indigo-100 mb-10 leading-relaxed">Join hundreds of schools already running on Academiq.<br>Setup takes less than 10 minutes.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-6">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-indigo-600 text-base font-bold rounded-xl hover:bg-indigo-50 transition-all shadow-xl">
                Start Your Free Trial →
            </a>
            <a href="#" class="inline-flex items-center gap-2 px-8 py-4 text-white border border-white/30 text-base font-medium rounded-xl hover:bg-white/10 transition-all">
                Schedule a Demo
            </a>
        </div>
        <p class="text-sm text-indigo-200/70">14-day free trial · No credit card required · Cancel anytime</p>
    </div>
</section>

{{-- ── Footer ── --}}
<footer class="border-t border-zinc-100 py-16 px-6 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-10 mb-14">
            <div class="col-span-2">
                <a href="/" class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black shadow-md shadow-indigo-600/20">Aq</div>
                    <span class="text-base font-semibold text-zinc-900">Academiq</span>
                </a>
                <p class="text-sm text-zinc-400 leading-relaxed max-w-xs">The modern standard for educational management. Built for schools that want to run smarter, not harder.</p>
            </div>
            @foreach([['Product',['Features','Pricing','Changelog','Roadmap']],['Modules',['Students','Exams','Fees','Hostel']],['Company',['About','Blog','Careers','Contact']],['Legal',['Terms','Privacy','Security','GDPR']]] as [$heading,$links])
            <div>
                <h4 class="text-xs font-bold text-zinc-900 uppercase tracking-widest mb-4">{{ $heading }}</h4>
                <ul class="space-y-3">
                    @foreach($links as $link)
                    <li><a href="#" class="text-sm text-zinc-400 hover:text-zinc-900 transition-colors">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        <div class="border-t border-zinc-100 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-zinc-400">© {{ date('Y') }} Academiq. All rights reserved.</p>
            <div class="flex gap-6">
                @foreach(['Twitter','LinkedIn','GitHub'] as $social)
                <a href="#" class="text-sm text-zinc-400 hover:text-zinc-900 transition-colors">{{ $social }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>

<script>
    const nav = document.getElementById('nav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('bg-white/90', window.scrollY > 20);
        nav.classList.toggle('backdrop-blur-xl', window.scrollY > 20);
        nav.classList.toggle('border-b', window.scrollY > 20);
        nav.classList.toggle('border-zinc-200/80', window.scrollY > 20);
        nav.classList.toggle('shadow-sm', window.scrollY > 20);
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    document.querySelectorAll('.faq-item').forEach(item => {
        item.addEventListener('click', () => {
            const answer = item.querySelector('.faq-answer');
            const toggle = item.querySelector('.faq-toggle');
            const isOpen = !answer.classList.contains('hidden');
            document.querySelectorAll('.faq-answer').forEach(a => a.classList.add('hidden'));
            document.querySelectorAll('.faq-toggle').forEach(t => t.textContent = '+');
            document.querySelectorAll('.faq-item').forEach(fi => fi.classList.remove('border-indigo-200','shadow-sm'));
            if (!isOpen) {
                answer.classList.remove('hidden');
                toggle.textContent = '−';
                item.classList.add('border-indigo-200', 'shadow-sm');
            }
        });
    });
</script>
</body>
</html>