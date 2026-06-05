<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentPromotion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

new #[Title('Student Promotions')]
class extends Component
{
    use Interactions, WithPagination;

    public $tab = 'students'; // 'students' or 'history'

    public $filterAcademicYear = '';

    public $filterClass = '';

    public array $selectedStudents = [];

    public bool $selectAll = false;

    public $promotionIdToDelete = null;

    public $editPromotionUuid = null;

    public function updatedFilterClass(): void
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedFilterAcademicYear(): void
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    /**
     * Fires whenever the select-all checkbox changes.
     * Selects or deselects every student on the current page.
     */
    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value
            ? $this->students->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedStudents(): void
    {
        $this->selectAll = count($this->selectedStudents) === $this->students->count();
    }

    public function toggleStudentSelection($studentId): void
    {
        if (in_array($studentId, $this->selectedStudents)) {
            $this->selectedStudents = array_values(
                array_diff($this->selectedStudents, [$studentId])
            );
        } else {
            $this->selectedStudents[] = $studentId;
        }

        $this->selectAll = count($this->selectedStudents) === $this->students->count();
    }

    #[Computed]
    public function classesWithSections()
    {
        return ClassModel::with(['sections' => fn ($q) => $q])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function students()
    {
        if (! $this->filterAcademicYear || ! $this->filterClass) {
            return Student::whereRaw('1 = 0')->paginate(15);
        }
        
        $query = Student::with(['class', 'section', 'academicYear']);

        if ($this->filterAcademicYear) {
            $query->where('academic_year_id', $this->filterAcademicYear);
        }

        if ($this->filterClass) {
            [$type, $id] = explode(':', $this->filterClass);
            if ($type === 'section') {
                $query->where('section_id', $id);
            } else {
                $query->where('class_id', $id);
            }
        }

        return $query->orderBy('first_name', 'asc')
            ->paginate(15);
    }

    #[Computed]
    public function promotionHistory()
    {
        return StudentPromotion::with(['student', 'fromClass', 'toClass', 'fromAcademicYear', 'toAcademicYear', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function openEditSlide(string $uuid): void
    {
        $this->editPromotionUuid = $uuid;
        $this->dispatch('edit-promotion', uuid: $uuid);
    }

    public function confirmDelete($id): void
    {
        $this->promotionIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this promotion record?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->promotionIdToDelete) {
            return;
        }

        StudentPromotion::findOrFail($this->promotionIdToDelete)
            ->delete();

        $this->promotionIdToDelete = null;
        unset($this->promotionHistory);

        Flux::toast(variant: 'success', text: __('Promotion record deleted.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Student Promotions') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage student class promotions and transfers.') }}</p>
        </div>
        {{-- <div class="flex gap-2">
            <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-promotion')" icon="plus">
                {{ __('New Promotion') }}
            </flux:button>
        </div> --}}
    </div>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="students" icon="users">
                {{ __('Promote Students') }}
            </flux:tab>
            <flux:tab name="history" icon="arrow-path">
                {{ __('Promotion History') }}
            </flux:tab>
        </flux:tabs>
    </flux:tab.group>

    @if ($tab === 'students')
        <flux:card>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model.live="filterAcademicYear">
                    <flux:select.option value="">{{ __('All Academic Years') }}</flux:select.option>
                    @forelse(AcademicYear::orderBy('name', 'desc')->get() as $ay)
                        <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                    @empty
                    @endforelse
                </flux:select>

                <flux:select label="{{ __('Class / Section') }}" variant="listbox" wire:model.live="filterClass">
                    <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                    @foreach($this->classesWithSections as $class)
                        @if($class->sections->isEmpty())
                            <flux:select.option value="class:{{ $class->id }}">{{ $class->name }}</flux:select.option>
                        @else
                            <flux:select.option value="class:{{ $class->id }}">{{ $class->name }}</flux:select.option>
                            @foreach($class->sections as $section)
                                <flux:select.option value="section:{{ $section->id }}">{{ $class->name }} — {{ $section->name }}</flux:select.option>
                            @endforeach
                        @endif
                    @endforeach
                </flux:select>
            </div>

            @if ($this->students->count())
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model.live="selectAll" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ count($this->selectedStudents) }} {{ __('selected') }}
                        </span>
                    </div>
                    @if (count($this->selectedStudents) > 0)
                        <flux:button
                            variant="primary"
                            class="button"
                            x-on:click="$tsui.open.slide('bulk-promote')"
                            icon="arrow-up-right"
                        >
                            {{ __('Promote Selected') }} ({{ count($this->selectedStudents) }})
                        </flux:button>
                    @endif
                </div>

                <div class="relative min-h-50"
                     wire:loading.class="opacity-60 pointer-events-none"
                     wire:target="filterAcademicYear, filterClass, selectAll, selectedStudents">

                    <div wire:loading
                         wire:target="filterAcademicYear, filterClass"
                         class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70 dark:bg-zinc-900/70 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="h-4 w-4 animate-spin text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ __('Loading...') }}
                        </div>
                    </div>

                    <flux:table :paginate="$this->students">
                        <flux:table.columns>
                            <flux:table.column style="width: 50px;">
                                <flux:checkbox wire:model.live="selectAll" />
                            </flux:table.column>
                            <flux:table.column>{{ __('Student Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Admission #') }}</flux:table.column>
                            <flux:table.column>{{ __('Current Class') }}</flux:table.column>
                            <flux:table.column>{{ __('Section') }}</flux:table.column>
                            <flux:table.column>{{ __('Academic Year') }}</flux:table.column>
                        </flux:table.columns>

                        @foreach ($this->students as $student)
                            <flux:table.rows>
                                <flux:table.row :key="$student->id">
                                    <flux:table.cell>
                                        <flux:checkbox
                                            wire:model.live="selectedStudents"
                                            value="{{ $student->id }}"
                                        />
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $student->first_name }} {{ $student->last_name }}</flux:table.cell>
                                    <flux:table.cell>{{ $student->admission_number ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>{{ $student->class?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>{{ $student->section?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>{{ $student->academicYear?->name ?? '-' }}</flux:table.cell>
                                </flux:table.row>
                            </flux:table.rows>
                        @endforeach
                    </flux:table>
                </div>
            @else
                <div class="p-6 text-center">
                    <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Students') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Select an academic year and class to view students.') }}</p>
                </div>
            @endif
        </flux:card>

    @else
        <flux:card>
            @if ($this->promotionHistory->count())
                <div class="relative min-h-50"
                     wire:loading.class="opacity-60 pointer-events-none"
                     wire:target="delete, confirmDelete">

                    <div wire:loading
                         wire:target="delete"
                         class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70 dark:bg-zinc-900/70 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="h-4 w-4 animate-spin text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ __('Loading...') }}
                        </div>
                    </div>

                    <flux:table :paginate="$this->promotionHistory">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Student') }}</flux:table.column>
                            <flux:table.column>{{ __('From Class') }}</flux:table.column>
                            <flux:table.column>{{ __('To Class') }}</flux:table.column>
                            <flux:table.column>{{ __('Academic Year') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column>{{ __('Processed By') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        @foreach ($this->promotionHistory as $promotion)
                            <flux:table.rows>
                                <flux:table.row :key="$promotion->id">
                                    <flux:table.cell>{{ $promotion->student?->first_name }} {{ $promotion->student?->last_name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>{{ $promotion->fromClass?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>{{ $promotion->toClass?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>{{ $promotion->fromAcademicYear?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="match($promotion->status) {
                                            'promoted'    => 'green',
                                            'detained'    => 'red',
                                            'transferred' => 'blue',
                                            default       => 'zinc',
                                        }">
                                            {{ str($promotion->status)->replace('_', ' ')->title() }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $promotion->processedBy?->username ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button
                                                size="sm"
                                                variant="subtle"
                                                icon="square-pen"
                                                wire:click="openEditSlide('{{ $promotion->uuid }}')"
                                                x-on:click="$tsui.open.slide('edit-promotion')"
                                            />
                                            <flux:button
                                                size="sm"
                                                variant="danger"
                                                icon="trash"
                                                wire:click="confirmDelete({{ $promotion->id }})"
                                            />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            </flux:table.rows>
                        @endforeach
                    </flux:table>
                </div>
            @else
                <div class="p-6 text-center">
                    <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Promotions') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No student promotions have been processed yet.') }}</p>
                </div>
            @endif
        </flux:card>
    @endif

    <x-slide id="bulk-promote" title="{{ __('Bulk Promote Students') }}" size="3xl">
        <livewire:pages::app.promotions.bulk-create :student-ids="$selectedStudents" />
    </x-slide>

    <x-slide id="edit-promotion" title="{{ __('Edit Promotion') }}" size="3xl">
        <livewire:pages::app.promotions.edit />
    </x-slide>
</div>