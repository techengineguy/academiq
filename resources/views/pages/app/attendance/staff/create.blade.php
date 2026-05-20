<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

new #[Title('Mark Staff Attendance')]
class extends Component {

    public string $date = '';
    public string $filterRole = '';
    public array $rows = [];
    public bool $employeesLoaded = false;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function loadEmployees(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
        ]);

        $query = User::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('role', ['teacher', 'staff'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($this->filterRole !== '') {
            $query->where('role', $this->filterRole);
        }

        $employees = $query->get();

        $existingAttendances = TeacherAttendance::where('tenant_id', Auth::user()->tenant_id)
            ->whereDate('date', $this->date)
            ->pluck('status', 'teacher_id');

        $this->rows = $employees->map(function (User $employee) use ($existingAttendances): array {
            return [
                'teacher_id' => $employee->id,
                'name' => trim($employee->first_name . ' ' . $employee->last_name),
                'email' => $employee->email,
                'role' => $employee->role,
                'status' => $existingAttendances[$employee->id] ?? 'present',
                'check_in_time' => '',
                'check_out_time' => '',
                'remarks' => '',
            ];
        })->values()->all();

        $this->employeesLoaded = true;
    }

    public function markAll(string $status): void
    {
        foreach ($this->rows as $index => $row) {
            $this->rows[$index]['status'] = $status;
        }
    }

    public function save(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.teacher_id' => ['required', 'exists:users,id'],
            'rows.*.status' => ['required', 'in:present,absent,late,half_day,on_leave'],
            'rows.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'rows.*.check_out_time' => ['nullable', 'date_format:H:i'],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function (): void {
            foreach ($this->rows as $row) {
                TeacherAttendance::updateOrCreate(
                    [
                        'tenant_id' => Auth::user()->tenant_id,
                        'teacher_id' => $row['teacher_id'],
                        'date' => $this->date,
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'status' => $row['status'],
                        'check_in_time' => $row['check_in_time'] !== '' ? $row['check_in_time'] : null,
                        'check_out_time' => $row['check_out_time'] !== '' ? $row['check_out_time'] : null,
                        'remarks' => $row['remarks'] !== '' ? $row['remarks'] : null,
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        Flux::toast(variant: 'success', text: __('Attendance marked successfully.'));

        $this->redirect(route('staff-attendance.index'), navigate: true);
    }
};
?>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Mark Staff Attendance') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Select a date and load employees to mark their attendance.') }}</p>
        </div>

        <flux:button variant="subtle" href="{{ route('staff-attendance.index') }}" wire:navigate icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:date-picker label="{{ __('Date') }}" wire:model.live="date" required />

            <flux:select
                label="{{ __('Role') }}"
                variant="listbox"
                wire:model.live="filterRole"
            >
                <flux:select.option value="">{{ __('All (Teachers & Staff)') }}</flux:select.option>
                <flux:select.option value="teacher">{{ __('Teachers Only') }}</flux:select.option>
                <flux:select.option value="staff">{{ __('Staff Only') }}</flux:select.option>
            </flux:select>

            <div class="flex items-end">
                <flux:button
                    wire:click="loadEmployees"
                    variant="primary"
                    class="button w-full"
                    icon="users"
                    :disabled="$date === ''"
                >
                    {{ __('Load Employees') }}
                </flux:button>
            </div>
        </div>
    </flux:card>

    @if($employeesLoaded)
        @if(count($rows) > 0)
            <form wire:submit="save" class="space-y-4">
                <flux:card>
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('Employees') }}
                                <flux:badge variant="info" class="ml-2">{{ count($rows) }}</flux:badge>
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Set attendance status for each employee.') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <flux:button type="button" size="sm" variant="subtle" wire:click="markAll('present')">
                                {{ __('All Present') }}
                            </flux:button>
                            <flux:button type="button" size="sm" variant="subtle" wire:click="markAll('absent')">
                                {{ __('All Absent') }}
                            </flux:button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($rows as $index => $row)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-zinc-700">
                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                    <div class="xl:col-span-2 flex flex-col justify-center">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                            <flux:badge size="sm" :color="$row['role'] === 'teacher' ? 'blue' : 'purple'">
                                                {{ ucfirst($row['role']) }}
                                            </flux:badge>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $row['email'] }}</span>
                                    </div>

                                    <div>
                                        <flux:select
                                            label="{{ __('Status') }}"
                                            variant="listbox"
                                            wire:model="rows.{{ $index }}.status"
                                        >
                                            <flux:select.option value="present">{{ __('Present') }}</flux:select.option>
                                            <flux:select.option value="absent">{{ __('Absent') }}</flux:select.option>
                                            <flux:select.option value="late">{{ __('Late') }}</flux:select.option>
                                            <flux:select.option value="half_day">{{ __('Half Day') }}</flux:select.option>
                                            <flux:select.option value="on_leave">{{ __('On Leave') }}</flux:select.option>
                                        </flux:select>
                                    </div>

                                    <div>
                                        <flux:time-picker
                                            label="{{ __('Check In') }}"
                                            wire:model="rows.{{ $index }}.check_in_time"
                                        />
                                    </div>

                                    <div>
                                        <flux:time-picker
                                            label="{{ __('Check Out') }}"
                                            wire:model="rows.{{ $index }}.check_out_time"
                                        />
                                    </div>

                                    <div>
                                        <flux:input
                                            label="{{ __('Remarks') }}"
                                            wire:model="rows.{{ $index }}.remarks"
                                            placeholder="{{ __('Optional') }}"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary" class="button" icon="check">
                        {{ __('Save Attendance') }}
                    </flux:button>
                    <flux:button type="button" variant="subtle" href="{{ route('staff-attendance.index') }}" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        @else
            <flux:card>
                <div class="p-6 text-center">
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Active Employees') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('There are no active teachers or staff members found.') }}</p>
                </div>
            </flux:card>
        @endif
    @endif
</div>
