<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\StudentParent;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Flux\Flux;

new #[Title('Edit Parent')]
class extends Component {

    public ?StudentParent $parent = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    public string $father_name = '';
    public string $father_phone = '';
    public string $father_email = '';
    public string $father_occupation = '';
    public string $father_annual_income = '';

    public string $mother_name = '';
    public string $mother_phone = '';
    public string $mother_email = '';
    public string $mother_occupation = '';
    public string $mother_annual_income = '';

    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_email = '';
    public string $guardian_relation = '';

    public array $student_ids = [];
    public array $relations = [];
    public string $primary_student_id = '';

    public function mount(int $id): void
    {
        $this->parent = StudentParent::with(['user', 'students'])
            ->findOrFail($id);

        $this->first_name = (string) ($this->parent->user?->first_name ?? '');
        $this->last_name = (string) ($this->parent->user?->last_name ?? '');
        $this->email = (string) ($this->parent->user?->email ?? '');
        $this->phone = (string) ($this->parent->user?->phone ?? '');
        $this->address = (string) ($this->parent->user?->address ?? '');

        $this->father_name = (string) ($this->parent->father_name ?? '');
        $this->father_phone = (string) ($this->parent->father_phone ?? '');
        $this->father_email = (string) ($this->parent->father_email ?? '');
        $this->father_occupation = (string) ($this->parent->father_occupation ?? '');
        $this->father_annual_income = (string) ($this->parent->father_annual_income ?? '');

        $this->mother_name = (string) ($this->parent->mother_name ?? '');
        $this->mother_phone = (string) ($this->parent->mother_phone ?? '');
        $this->mother_email = (string) ($this->parent->mother_email ?? '');
        $this->mother_occupation = (string) ($this->parent->mother_occupation ?? '');
        $this->mother_annual_income = (string) ($this->parent->mother_annual_income ?? '');

        $this->guardian_name = (string) ($this->parent->guardian_name ?? '');
        $this->guardian_phone = (string) ($this->parent->guardian_phone ?? '');
        $this->guardian_email = (string) ($this->parent->guardian_email ?? '');
        $this->guardian_relation = (string) ($this->parent->guardian_relation ?? '');

        $this->student_ids = $this->parent->students->pluck('id')->map(fn ($id) => (string) $id)->all();

        foreach ($this->parent->students as $student) {
            $this->relations[$student->id] = $student->pivot->relation ?? 'father';
            if ($student->pivot->is_primary) {
                $this->primary_student_id = (string) $student->id;
            }
        }
    }

    #[Computed]
    public function students()
    {
        return Student::where('status', 'active')
            ->with(['user', 'class'])
            ->orderBy('roll_number')
            ->get();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->parent->user_id)],
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
            // Update user account
            $this->parent->user?->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
                'address' => $validated['address'] ?: null,
            ]);

            // Update parent record
            $this->parent->update([
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

            // Sync children
            $pivotData = [];
            foreach ($validated['student_ids'] ?? [] as $studentId) {
                $pivotData[$studentId] = [
                    'tenant_id' => \Spatie\Multitenancy\Models\Tenant::current()->uuid,
                    'uuid' => Str::uuid(),
                    'relation' => $this->relations[$studentId] ?? 'father',
                    'is_primary' => (string) $studentId === $this->primary_student_id,
                ];
            }
            $this->parent->students()->sync($pivotData);
        });

        Flux::toast(variant: 'success', text: __('Parent updated successfully.'));

        $this->redirect(route('parents.index'), navigate: true);
    }
};
?>
<div class="space-y-6">
    @if($this->parent)
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Parent') }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Update parent information and child links.') }}</p>
            </div>

            <flux:button variant="subtle" href="{{ route('parents.index') }}" wire:navigate icon="arrow-left">
                {{ __('Back') }}
            </flux:button>
        </div>

        <flux:card>
            <form wire:submit="update" class="space-y-8">
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
                            <flux:input label="{{ __('Relation to Student') }}" wire:model="guardian_relation" />
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Linked Children') }}</h2>
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
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <flux:button type="submit" variant="primary" class="button">{{ __('Update') }}</flux:button>
                    <flux:button variant="subtle" href="{{ route('parents.index') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    @endif
</div>
