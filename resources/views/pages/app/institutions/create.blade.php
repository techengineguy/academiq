<?php

use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Create Institution')]
#[Layout('layouts.app')]
class extends Component {
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = '';
    public string $postal_code = '';
    public string $website = '';
    public string $description = '';

    public function save(): void
    {
        $validated = $this->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'website'     => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Generate a unique code from the institution name.
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 8));
        $code = $base;
        $suffix = 1;
        while (Institution::where('code', $code)->exists()) {
            $code = $base.$suffix;
            $suffix++;
        }

        $institution = Institution::create([
            ...$validated,
            'uuid'   => (string) Str::uuid(),
            'code'   => $code,
            'status' => 'active',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->adminInstitutions()->syncWithoutDetaching([$institution->id]);

        session()->put('active_institution_id', $institution->id);
        $institution->makeCurrent();

        $this->redirect(route('subscription.plans'));
    }
};
?>

<div class="mx-auto max-w-2xl py-8">
    <flux:heading size="xl">{{ __('Create New Institution') }}</flux:heading>
    <flux:text class="mb-6 mt-1">{{ __('Set up a new institution. You will be taken to select a subscription plan after creation.') }}</flux:text>

    <flux:card>
        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label required>{{ __('Institution Name') }}</flux:label>
                <flux:input wire:model="name" placeholder="e.g. Greenfield Academy" />
                <flux:error name="name" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input type="email" wire:model="email" />
                    <flux:error name="email" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model="phone" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Address') }}</flux:label>
                <flux:input wire:model="address" />
                <flux:error name="address" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input wire:model="city" />
                    <flux:error name="city" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('State') }}</flux:label>
                    <flux:input wire:model="state" />
                    <flux:error name="state" />
                </flux:field>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('Country') }}</flux:label>
                    <flux:input wire:model="country" />
                    <flux:error name="country" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Postal Code') }}</flux:label>
                    <flux:input wire:model="postal_code" />
                    <flux:error name="postal_code" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Website') }}</flux:label>
                <flux:input type="url" wire:model="website" placeholder="https://" />
                <flux:error name="website" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea wire:model="description" rows="3" />
                <flux:error name="description" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button :href="route('dashboard')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Create Institution') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
