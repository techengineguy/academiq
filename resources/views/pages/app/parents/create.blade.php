<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\StudentParent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Register Parent')]
class extends Component {

    // User fields
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    // Father details
    public string $father_name = '';
    public string $father_phone = '';
    public string $father_email = '';
    public string $father_occupation = '';
    public string $father_annual_income = '';

    // Mother details
    public string $mother_name = '';
    public string $mother_phone = '';
    public string $mother_email = '';
    public string $mother_occupation = '';
    public string $mother_annual_income = '';

    // Guardian details
    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_email = '';
    public string $guardian_relation = '';

    // Children to link
    public array $student_ids = [];
    public array $relations = []; // [student_id => 'father|mother|guardian']
    public string $primary_student_id = '';

    #[Computed]
    public function students()
    {
        return Student::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'active')
            ->with(['user', 'class'])
            ->orderBy('roll_number')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:50'],
            'father_email' => ['nullable', 'email'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'father_annual_income' => ['nullable', 'numeric'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:50'],
            'mother_email' => ['nullable', 'email'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'mother_annual_income' => ['nullable', 'numeric'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:50'],
            'guardian_email' => ['nullable', 'email'],
            'guardian_relation' => ['nullable', 'string', 'max:100'],
            'student_ids' => ['array'],
            'student_ids.*' => ['exists:students,id'],
            'relations' => ['array'],
        ]);

        DB::transaction(function () use ($validated): void {
            // Create the user account
            $user = User::create([
                'tenant_id' => Auth::user()->tenant_id,
                'uuid' => Str::uuid(),
                'institution_id' => Auth::user()->institution_id,
                'username' => Str::slug($validated['first_name'] . '-' . $validated['last_name']) . '-' . Str::random(4),
                'email' => $validated['email'],
                'password' => Hash::make('password'),
                'role' => 'parent',
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'] ?: null,
                'address' => $validated['address'] ?: null,
                'is_active' => true,
            ]);

            // Create the parent record
            $parent = StudentParent::create([
                'tenant_id' => Auth::user()->tenant_id,
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'father_name' => $validated['father_name'] ?: null,
                'father_phone' => $validated['father_phone'] ?: null,
                'father_email' => $validated['father_email'] ?: null,
                'father_occupation' => $validated['father_occupation'] ?: null,
                'father_annual_income' => $validated['father_annual_income'] ?: null,
                'mother_name' => $validated['mother_name'] ?: null,
                'mother_phone' => $validated['mother_phone'] ?: null,
                'mother_email' => $validated['mother_email'] ?: null,
                'mother_occupation' => $validated['mother_occupation'] ?: null,
                'mother_annual_income' => $validated['mother_annual_income'] ?: null,
                'guardian_name' => $validated['guardian_name'] ?: null,
                'guardian_phone' => $validated['guardian_phone'] ?: null,
                'guardian_email' => $validated['guardian_email'] ?: null,
                'guardian_relation' => $validated['guardian_relation'] ?: null,
            ]);

            // Link children
            if (! empty($validated['student_ids'])) {
                $pivotData = [];
                foreach ($validated['student_ids'] as $studentId) {
                    $pivotData[$studentId] = [
                        'tenant_id' => Auth::user()->tenant_id,
                        'uuid' => Str::uuid(),
                        'relation' => $this->relations[$studentId] ?? 'father',
                        'is_primary' => (string) $studentId === $this->primary_student_id,
                    ];
                }
                $parent->students()->attach($pivotData);
            }
        });

        Flux::toast(variant: 'success', text: __('Parent registered successfully. Default password is "password".'));

        $this->redirect(route('parents.index'), navigate: true);
    }
};
?>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Register Parent') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Create a parent account and link them to students.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('parents.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-8">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Account Information') }}</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __('First Name') }}" wire:model="first_name" required />
                        <flux:input label="{{ __('Last Name') }}" wire:model="last_name" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __('Email') }}" type="email" wire:model="email" required />
                        <flux:input label="{{ __('Phone') }}" wire:model="phone" />
                    </div>
                    <flux:textarea label="{{ __('Address') }}" wire:model="address" rows="2" />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Father Information') }}</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __("Father's Name") }}" wire:model="father_name" />
                        <flux:input label="{{ __("Father's Phone") }}" wire:model="father_phone" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __("Father's Email") }}" type="email" wire:model="father_email" />
                        <flux:input label="{{ __('Occupation') }}" wire:model="father_occupation" />
                    </div>
                    <flux:input label="{{ __('Annual Income') }}" type="text" inputmode="decimal" wire:model="father_annual_income" />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Mother Information') }}</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __("Mother's Name") }}" wire:model="mother_name" />
                        <flux:input label="{{ __("Mother's Phone") }}" wire:model="mother_phone" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __("Mother's Email") }}" type="email" wire:model="mother_email" />
                        <flux:input label="{{ __('Occupation') }}" wire:model="mother_occupation" />
                    </div>
                    <flux:input label="{{ __('Annual Income') }}" type="text" inputmode="decimal" wire:model="mother_annual_income" />
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Guardian Information (Optional)') }}</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __("Guardian's Name") }}" wire:model="guardian_name" />
                        <flux:input label="{{ __("Guardian's Phone") }}" wire:model="guardian_phone" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="{{ __("Guardian's Email") }}" type="email" wire:model="guardian_email" />
                        <flux:input label="{{ __('Relation to Student') }}" wire:model="guardian_relation" placeholder="{{ __('e.g., Uncle, Aunt') }}" />
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Link Children') }}</h2>
                <p class="text-xs text-gray-500 mb-3">{{ __('Select students to link this parent to. Mark one as primary contact.') }}</p>

                <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 dark:border-zinc-700 p-4 space-y-3">
                    @foreach($this->students as $student)
                        <div class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 dark:hover:bg-zinc-800">
                            <flux:checkbox
                                value="{{ $student->id }}"
                                wire:model.live="student_ids"
                                label="{{ $student->user?->first_name }} {{ $student->user?->last_name }} ({{ $student->class?->name ?? '-' }})"
                            />
                            @if(in_array($student->id, $student_ids) || in_array((string) $student->id, $student_ids))
                                <div class="flex items-center gap-2 ml-auto">
                                    <select wire:model="relations.{{ $student->id }}" class="text-xs rounded border-gray-300 dark:border-zinc-600 dark:bg-zinc-800">
                                        <option value="father">{{ __('Father') }}</option>
                                        <option value="mother">{{ __('Mother') }}</option>
                                        <option value="guardian">{{ __('Guardian') }}</option>
                                    </select>
                                    <label class="flex items-center gap-1 text-xs">
                                        <input type="radio" wire:model="primary_student_id" value="{{ $student->id }}" />
                                        {{ __('Primary') }}
                                    </label>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if($this->students->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-4">{{ __('No active students to link.') }}</p>
                    @endif
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" class="button">{{ __('Register Parent') }}</flux:button>
                <flux:button variant="subtle" href="{{ route('parents.index') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
