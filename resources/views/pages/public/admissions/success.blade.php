<?php

use App\Models\AdmissionApplication;
use App\Models\Institution;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\URL;

new #[Title('Application Submitted'), Layout('layouts::auth')]
class extends Component {

    public Institution $institution;
    public AdmissionApplication $application;

    public function mount(Institution $institution, AdmissionApplication $application): void
    {
        // Verify application belongs to the institution
        if ($application->tenant_id !== $institution->uuid) {
            abort(404);
        }

        $this->institution = $institution;
        $this->application = $application;
    }

    public function render()
    {
        return view('pages.public.admissions.success');
    }
}
?>

<div class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 lg:flex-row lg:px-6">
    <section class="flex-1 rounded-3xl bg-linear-to-br from-emerald-950 via-emerald-900 to-emerald-800 p-8 text-white shadow-2xl ring-1 ring-white/10">
        <div class="max-w-xl space-y-6">
            <div class="inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white/80 ring-1 ring-white/15">
                {{ $institution->name }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <flux:icon name="check-circle" class="h-12 w-12 text-emerald-300" />
                    <h1 class="text-4xl font-bold tracking-tight">{{ __('Application Submitted') }}</h1>
                </div>
                <p class="mt-4 text-base leading-7 text-white/75">
                    {{ __('Your application has been successfully submitted. Please keep your reference number for future correspondence.') }}
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                    <p class="text-sm text-white/60">{{ __('Reference Number') }}</p>
                    <p class="mt-1 font-mono text-2xl font-bold text-emerald-300">{{ $application->application_number }}</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                    <p class="text-sm text-white/60">{{ __('Status') }}</p>
                    <p class="mt-1 text-lg font-semibold">{{ __('Pending Review') }}</p>
                </div>
            </div>
            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                <p class="text-sm text-white/60">{{ __('Submitted By') }}</p>
                <p class="mt-2 text-base text-white/90">
                    <span class="block">{{ $application->student_name }}</span>
                    <span class="text-sm text-white/60">{{ $application->parent_email }}</span>
                </p>
            </div>
        </div>
    </section>

    <section class="flex-1 rounded-3xl bg-white p-6 shadow-xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800 lg:p-8">
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('What Happens Next?') }}</h2>
                <div class="mt-4 space-y-4">
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 shrink-0">
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">1</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ __('Review') }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Your application will be reviewed by our admissions team.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 shrink-0">
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">2</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ __('Assessment') }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('We may schedule a test or interview if needed.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 shrink-0">
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">3</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ __('Decision') }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('You will be notified of the decision via email.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Questions?') }}</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Contact :institution at :email', ['institution' => $institution->name, 'email' => $institution->email]) }}
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <flux:button href="{{ route('home') }}" variant="subtle" class="w-full">
                    {{ __('Back to Home') }}
                </flux:button>
            </div>
        </div>
    </section>
</div>
