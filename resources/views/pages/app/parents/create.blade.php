<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Register Parent')]
class extends Component {

    // User account fields
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $username = '';
    public string $phone = '';
    public string $password = '';

    // Parent details
    public string $primary_relation = 'father';

    // Father details
    public string $father_name = '';
    public string $father_phone = '';
    public string $father_email = '';
    public string $father_occupation = '';

    // Mother details
    public string $mother_name = '';
    public string $mother_phone = '';
    public string $mother_email = '';
    public string $mother_occupation = '';

    // Guardian details
    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_email = '';
    public string $guardian_relation = '';

    public string $studentSearch = '';
    public string $studentClassFilter = '';

    // Children to link
    public array $student_ids = [];

    #[Computed]
    public function classes()
    {
        return \App\Models\ClassModel::whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function students()
    {
        return Student::with(['user', 'class'])
            ->when($this->studentSearch !== '', function ($query) {
                $search = '%' . $this->studentSearch . '%';
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search))
                        ->orWhere('admission_number', 'like', $search)
                        ->orWhere('roll_number', 'like', $search);
                });
            })
            ->when($this->studentClassFilter !== '', fn ($q) => $q->where('class_id', $this->studentClassFilter))
            ->orderBy('roll_number')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
            'primary_relation' => ['required', 'in:father,mother,guardian'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:50'],
            'father_email' => ['nullable', 'email'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:50'],
            'mother_email' => ['nullable', 'email'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:50'],
            'guardian_email' => ['nullable', 'email'],
            'guardian_relation' => ['nullable', 'string', 'max:100'],
            'student_ids' => ['array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            // Create user account
            $user = User::create([
                'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                'uuid' => Str::uuid(),
                'institution_id' => Auth::user()->institution_id,
                'username' => $validated['username'] ?: Str::slug($validated['first_name'] . '-' . $validated['last_name']) . '-' . random_int(100, 999),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
                'password' => Hash::make($validated['password']),
                'role' => 'parent',
                'is_active' => true,
            ]);

            // Create parent record
            $parent = StudentParent::create([
                'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'father_name' => $validated['father_name'] ?: null,
                'father_phone' => $validated['father_phone'] ?: null,
                'father_email' => $validated['father_email'] ?: null,
                'father_occupation' => $validated['father_occupation'] ?: null,
                'mother_name' => $validated['mother_name'] ?: null,
                'mother_phone' => $validated['mother_phone'] ?: null,
                'mother_email' => $validated['mother_email'] ?: null,
                'mother_occupation' => $validated['mother_occupation'] ?: null,
                'guardian_name' => $validated['guardian_name'] ?: null,
                'guardian_phone' => $validated['guardian_phone'] ?: null,
                'guardian_email' => $validated['guardian_email'] ?: null,
                'guardian_relation' => $validated['guardian_relation'] ?: null,
            ]);

            // Link children with the relation type
            if (! empty($validated['student_ids'])) {
                $pivotData = collect($validated['student_ids'])->mapWithKeys(fn ($studentId) => [
                    $studentId => [
                        'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                        'uuid' => Str::uuid(),
                        'relation' => $validated['primary_relation'],
                        'is_primary' => true,
                    ],
                ])->all();
                $parent->students()->attach($pivotData);
            }
        });

        Flux::toast(variant: 'success', text: __('Parent registered successfully.'));

        $this->redirect(route('parents.index'), navigate: true);
    }
};
?>
<div class="space-y-6 py-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Register Parent') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Create a parent account and link them to their children.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('parents.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-6">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Account Information') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="{{ __('First Name') }}" wire:model="first_name" required />
                    <flux:input label="{{ __('Last Name') }}" wire:model="last_name" required />
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <flux:input label="{{ __('Email') }}" type="email" wire:model="email" required />
                    <flux:input label="{{ __('Phone') }}" wire:model="phone" />
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <flux:input label="{{ __('Username') }}" wire:model="username" placeholder="{{ __('Auto-generated if blank') }}" />
                    <flux:input label="{{ __('Password') }}" type="password" wire:model="password" required />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Father Details') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="{{ __('Father Name') }}" wire:model="father_name" />
                    <flux:input label="{{ __('Father Phone') }}" wire:model="father_phone" />
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <flux:input label="{{ __('Father Email') }}" type="email" wire:model="father_email" />
                    <flux:input label="{{ __('Father Occupation') }}" wire:model="father_occupation" />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Mother Details') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="{{ __('Mother Name') }}" wire:model="mother_name" />
                    <flux:input label="{{ __('Mother Phone') }}" wire:model="mother_phone" />
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <flux:input label="{{ __('Mother Email') }}" type="email" wire:model="mother_email" />
                    <flux:input label="{{ __('Mother Occupation') }}" wire:model="mother_occupation" />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Guardian Details') }} <span class="text-xs font-normal text-gray-500">({{ __('Optional') }})</span></h2>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="{{ __('Guardian Name') }}" wire:model="guardian_name" />
                    <flux:input label="{{ __('Guardian Relation') }}" wire:model="guardian_relation" placeholder="{{ __('e.g., Uncle, Grandfather') }}" />
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <flux:input label="{{ __('Guardian Phone') }}" wire:model="guardian_phone" />
                    <flux:input label="{{ __('Guardian Email') }}" type="email" wire:model="guardian_email" />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Link to Children') }}</h2>

                <flux:select label="{{ __('Primary Relation') }}" variant="listbox" wire:model="primary_relation" required>
                    <flux:select.option value="father">{{ __('Father') }}</flux:select.option>
                    <flux:select.option value="mother">{{ __('Mother') }}</flux:select.option>
                    <flux:select.option value="guardian">{{ __('Guardian') }}</flux:select.option>
                </flux:select>

                <div class="mt-4 max-h-72 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700 p-4 space-y-2">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <flux:input
                            wire:model.live.debounce.300ms="studentSearch"
                            placeholder="{{ __('Search by name, admission no.') }}"
                            icon="magnifying-glass"
                        />
                        <flux:select variant="listbox" wire:model.live="studentClassFilter" placeholder="{{ __('All Classes') }}">
                            <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                            @foreach($this->classes as $class)
                                <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    @forelse($this->students as $student)
                        <flux:checkbox
                            label="{{ $student->user?->first_name }} {{ $student->user?->last_name }} - {{ $student->class?->name ?? '-' }} ({{ $student->admission_number }})"
                            value="{{ $student->id }}"
                            wire:model="student_ids"
                        />
                    @empty
                        <p class="text-sm text-gray-500 text-center py-2">{{ __('No students available') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Register Parent') }}</flux:button>
                <flux:button variant="subtle" href="{{ route('parents.index') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
