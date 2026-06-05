<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Institution Settings')] class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $code = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = '';
    public string $postal_code = '';
    public string $website = '';
    public string $description = '';
    public $logo;

    public function mount(): void
    {
        $institution = Auth::user()->institution;

        if (!$institution) {
            abort(403, 'No institution is associated with your account.');
        }

        $this->name = $institution->name ?? '';
        $this->code = $institution->code ?? '';
        $this->email = $institution->email ?? '';
        $this->phone = $institution->phone ?? '';
        $this->address = $institution->address ?? '';
        $this->city = $institution->city ?? '';
        $this->state = $institution->state ?? '';
        $this->country = $institution->country ?? '';
        $this->postal_code = $institution->postal_code ?? '';
        $this->website = $institution->website ?? '';
        $this->description = $institution->description ?? '';
    }

    public function updateInstitution(): void
    {
        $institution = Auth::user()->institution;

        if (!$institution) {
            Flux::toast(variant: 'danger', text: __('No institution found.'));
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:institutions,code,' . $institution->id],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->logo) {
            $logoPath = $this->logo->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $institution->update($validated);

        Flux::toast(variant: 'success', text: __('Institution settings updated successfully.'));

        $this->logo = null;
    }

    #[Computed]
    public function institution()
    {
        return Auth::user()->institution;
    }

    #[Computed]
    public function canEdit(): bool
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'super_admin']);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Institution Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Institution Information')" :subheading="__('Update your institution details and contact information')">
        @if (!$this->canEdit)
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-700/50 dark:bg-amber-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        {{ __('You do not have permission to edit institution settings.') }}
                    </p>
                </div>
            </div>
        @endif

        <form wire:submit="updateInstitution" class="my-6 w-full space-y-6">
            {{-- Basic Information --}}
            <div class="space-y-4">
                <flux:heading size="lg" class="mb-2">{{ __('Basic Information') }}</flux:heading>

                <flux:input 
                    wire:model="name" 
                    :label="__('Institution Name')" 
                    type="text" 
                    required 
                    :disabled="!$this->canEdit"
                />

                <flux:input 
                    wire:model="code" 
                    :label="__('Institution Code')" 
                    type="text" 
                    required 
                    :disabled="!$this->canEdit"
                    description="{{ __('Unique identifier for your institution') }}"
                />

                <flux:textarea 
                    wire:model="description" 
                    :label="__('Description')" 
                    rows="3"
                    :disabled="!$this->canEdit"
                />

                @if ($this->institution?->logo)
                    <div>
                        <flux:label>{{ __('Current Logo') }}</flux:label>
                        <img 
                            src="{{ asset('storage/' . $this->institution->logo) }}" 
                            alt="{{ $this->institution->name }}" 
                            class="mt-2 h-20 w-20 rounded object-cover border border-zinc-200 dark:border-zinc-700"
                        >
                    </div>
                @endif

                <flux:input 
                    wire:model="logo" 
                    :label="__('Upload New Logo')" 
                    type="file" 
                    accept="image/*"
                    :disabled="!$this->canEdit"
                    description="{{ __('Maximum file size: 2MB') }}"
                />
            </div>

            <flux:separator />

            {{-- Contact Information --}}
            <div class="space-y-4">
                <flux:heading size="lg" class="mb-2">{{ __('Contact Information') }}</flux:heading>

                <flux:input 
                    wire:model="email" 
                    :label="__('Email Address')" 
                    type="email" 
                    required 
                    :disabled="!$this->canEdit"
                />

                <flux:input 
                    wire:model="phone" 
                    :label="__('Phone Number')" 
                    type="tel" 
                    :disabled="!$this->canEdit"
                />

                <flux:input 
                    wire:model="website" 
                    :label="__('Website')" 
                    type="url" 
                    :disabled="!$this->canEdit"
                    placeholder="https://"
                />
            </div>

            <flux:separator />

            {{-- Address Information --}}
            <div class="space-y-4">
                <flux:heading size="lg" class="mb-2">{{ __('Address') }}</flux:heading>

                <flux:textarea 
                    wire:model="address" 
                    :label="__('Street Address')" 
                    rows="2"
                    :disabled="!$this->canEdit"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input 
                        wire:model="city" 
                        :label="__('City')" 
                        type="text" 
                        :disabled="!$this->canEdit"
                    />

                    <flux:input 
                        wire:model="state" 
                        :label="__('State/Province')" 
                        type="text" 
                        :disabled="!$this->canEdit"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input 
                        wire:model="country" 
                        :label="__('Country')" 
                        type="text" 
                        :disabled="!$this->canEdit"
                    />

                    <flux:input 
                        wire:model="postal_code" 
                        :label="__('Postal Code')" 
                        type="text" 
                        :disabled="!$this->canEdit"
                    />
                </div>
            </div>

            @if ($this->canEdit)
                <div class="flex items-center gap-4 pt-4">
                    <flux:button variant="primary" type="submit">
                        {{ __('Save Changes') }}
                    </flux:button>
                </div>
            @endif
        </form>
    </x-pages::settings.layout>
</section>
