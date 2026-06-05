<?php

use App\Models\StudentScholarship;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Student;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

new #[Title('Award Scholarships')]
class extends Component
{
    use Interactions, WithPagination;

    public $filterAcademicYear = '';

    public $filterStudent = '';

    public $filterStatus = '';

    #[Computed]
    public function awards()
    {
        $query = StudentScholarship::with(['student', 'scholarship', 'academicYear', 'grantedBy']);

        if ($this->filterAcademicYear) {
            $query->where('academic_year_id', $this->filterAcademicYear);
        }

        if ($this->filterStudent) {
            $query->whereHas('student', function ($q) {
                $q->where('id', $this->filterStudent);
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public $awardIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->awardIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this scholarship award?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->awardIdToDelete) {
            return;
        }

        StudentScholarship::findOrFail($this->awardIdToDelete)->delete();

        $this->awardIdToDelete = null;
        unset($this->awards);

        Flux::toast(variant: 'success', text: __('Scholarship award deleted.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Award Scholarships') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage student scholarship awards and allocations.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-award')" icon="plus">
                {{ __('Award Scholarship') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        <div class="grid grid-cols-3 gap-4 mb-6">
            <flux:select label="{{ __('Academic Year') }}" variant="listbox" wire:model.live="filterAcademicYear">
                <flux:select.option value="">{{ __('All Academic Years') }}</flux:select.option>
                @forelse(AcademicYear::orderBy('name', 'desc')->get() as $ay)
                    <flux:select.option value="{{ $ay->id }}">{{ $ay->name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select label="{{ __('Student') }}" variant="listbox" wire:model.live="filterStudent">
                <flux:select.option value="">{{ __('All Students') }}</flux:select.option>
                @forelse(Student::orderBy('first_name')->get() as $student)
                    <flux:select.option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</flux:select.option>
                @empty
                @endforelse
            </flux:select>

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model.live="filterStatus">
                <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="revoked">{{ __('Revoked') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->awards->count())
            <flux:table :paginate="$this->awards">
                <flux:table.columns>
                    <flux:table.column>{{ __('Student') }}</flux:table.column>
                    <flux:table.column>{{ __('Scholarship') }}</flux:table.column>
                    <flux:table.column>{{ __('Academic Year') }}</flux:table.column>
                    <flux:table.column>{{ __('Discount Amount') }}</flux:table.column>
                    <flux:table.column>{{ __('Granted Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->awards as $award)
                    <flux:table.rows>
                        <flux:table.row :key="$award->id">
                            <flux:table.cell>{{ $award->student?->first_name }} {{ $award->student?->last_name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $award->scholarship?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $award->academicYear?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($award->discount_amount, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ optional($award->granted_date)->format('Y-m-d') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$award->status === 'active' ? 'green' : ($award->status === 'revoked' ? 'red' : 'yellow')">
                                    {{ str($award->status)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button
                                        size="sm"
                                        variant="subtle"
                                        x-on:click="$tsui.open.slide('edit-award'), $wire.dispatch('edit-award', { uuid: '{{ $award->uuid }}' })"
                                        icon="square-pen"
                                    />
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $award->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                @endforeach
            </flux:table>
        @else
            <div class="p-6 text-center">
                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Scholarship Awards') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Start by awarding scholarships to students.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-award" title="{{ __('Award Scholarship') }}" size="3xl">
        <livewire:pages::app.scholarships.awards.create />
    </x-slide>

    <x-slide id="edit-award" title="{{ __('Edit Scholarship Award') }}" size="3xl">
        <livewire:pages::app.scholarships.awards.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>
