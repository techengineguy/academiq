<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6" x-data="{
        step: 1,
        errors: {},
        validateStep1() {
            this.errors = {};
            const fields = [
                { id: 'institution_name', label: 'Institution Name' },
                { id: 'institution_code', label: 'Institution Code' },
            ];
            fields.forEach(f => {
                const el = document.querySelector(`[name=${f.id}]`);
                if (!el || !el.value.trim()) {
                    this.errors[f.id] = `${f.label} is required.`;
                }
            });
            return Object.keys(this.errors).length === 0;
        },
        next() {
            if (this.validateStep1()) this.step = 2;
        }
    }">
    <x-auth-header :title="__('Create your account')" :description="__('Fill in the details below to create your account')" />
        
        <form method="POST" action="{{ route('register.store') }}" class="w-full flex flex-col gap-6 bg-white dark:bg-zinc-900 rounded-lg shadow-xl p-8">
            @csrf

            <!-- Step 1: Institution Details -->
            <div x-show="step === 1" x-transition>
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex-1 h-px bg-zinc-300 dark:bg-zinc-700"></div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white whitespace-nowrap">{{ __('Institution Details') }}</h3>
                    <div class="flex-1 h-px bg-zinc-300 dark:bg-zinc-700"></div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <flux:input
                            name="institution_name"
                            :label="__('Institution Name')"
                            :value="old('institution_name')"
                            type="text"
                            required
                            autofocus
                            :placeholder="__('e.g., Central High School')"
                        />
                        <p x-show="errors.institution_name" x-text="errors.institution_name"
                            class="text-red-500 text-xs mt-1"></p>
                    </div>
                    <div>
                        <flux:input
                            name="institution_code"
                            :label="__('Institution Code')"
                            :value="old('institution_code')"
                            type="text"
                            required
                            :placeholder="__('e.g., CHS001')"
                        />
                        <p x-show="errors.institution_code" x-text="errors.institution_code"
                            class="text-red-500 text-xs mt-1"></p>
                    </div>
                    <flux:input
                        name="institution_email"
                        :label="__('Institution Email')"
                        :value="old('institution_email')"
                        type="email"
                        :placeholder="__('institution@example.com')"
                    />
                    <flux:input
                        name="institution_phone"
                        :label="__('Institution Phone')"
                        :value="old('institution_phone')"
                        type="tel"
                        :placeholder="__('e.g., +234000000000')"
                    />
                    <flux:input
                        name="institution_city"
                        :label="__('City')"
                        :value="old('institution_city')"
                        type="text"
                        :placeholder="__('City')"
                    />
                    <flux:input
                        name="institution_state"
                        :label="__('State')"
                        :value="old('institution_state')"
                        type="text"
                        :placeholder="__('State')"
                    />
                    <flux:input
                        name="institution_country"
                        :label="__('Country')"
                        :value="old('institution_country')"
                        type="text"
                        :placeholder="__('Country')"
                    />
                    <flux:input
                        name="institution_postal_code"
                        :label="__('Postal Code')"
                        :value="old('institution_postal_code')"
                        type="text"
                        :placeholder="__('Postal Code')"
                    />
                </div>

                <div class="mt-6">
                    <flux:textarea
                        name="institution_address"
                        :label="__('Institution Address')"
                        :value="old('institution_address')"
                        :placeholder="__('Street address')"
                    />
                </div>
            </div>

            <!-- Step 2: Administrator Account -->
            <div x-show="step === 2" x-transition>
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex-1 h-px bg-zinc-300 dark:bg-zinc-700"></div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white whitespace-nowrap">{{ __('Administrator Account') }}</h3>
                    <div class="flex-1 h-px bg-zinc-300 dark:bg-zinc-700"></div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <flux:input
                        name="first_name"
                        :label="__('First Name')"
                        :value="old('first_name')"
                        type="text"
                        required
                        :placeholder="__('First name')"
                    />
                    <flux:input
                        name="last_name"
                        :label="__('Last Name')"
                        :value="old('last_name')"
                        type="text"
                        required
                        :placeholder="__('Last name')"
                    />
                    <flux:input
                        name="username"
                        :label="__('Username')"
                        :value="old('username')"
                        type="text"
                        required
                        autocomplete="username"
                        :placeholder="__('Choose a unique username')"
                    />
                    <flux:input
                        name="email"
                        :label="__('Email Address')"
                        :value="old('email')"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="admin@example.com"
                    />
                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Password')"
                        viewable
                    />
                    <flux:input
                        name="password_confirmation"
                        :label="__('Confirm Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Confirm password')"
                        viewable
                    />
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between gap-3">
                <x-button type="button" @click="step = 1" x-show="step === 2" variant="outline">{{ __('Back') }}</x-button>
                <div class="flex gap-3 ms-auto">
                    <x-button type="button" @click="next()" x-show="step === 1">{{ __('Next') }}</x-button>
                    <x-button type="submit" x-show="step === 2" loading>{{ __('Register') }}</x-button>
                </div>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>