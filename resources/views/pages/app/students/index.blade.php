<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Student;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Students')]
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function students()
    {
        return Student::with(['user','class','section','academicYear'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public $studentIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->studentIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this student?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->studentIdToDelete) return;

        Student::findOrFail($this->studentIdToDelete)->delete();

        $this->studentIdToDelete = null;
        unset($this->students);

        Flux::toast(variant: 'success', text: __('Student deleted successfully, Restore from trash.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Students') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage students and their information.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button class="button w-fit" x-on:click="$tsui.open.slide('create-student')" icon="plus">
                {{ __('New Student') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        @if($this->students->count())
            <flux:table :paginate="$this->students">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Admission No') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Section') }}</flux:table.column>
                    <flux:table.column>{{ __('Roll') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->students as $student)
                    <flux:table.rows>
                        <flux:table.row :key="$student->id">
                            <flux:table.cell>{{ $student->user?->first_name }} {{ $student->user?->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $student->user?->email }}</flux:table.cell>
                            <flux:table.cell>{{ $student->admission_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $student->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $student->section?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $student->roll_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$student->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($student->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-student'), $wire.dispatch('edit-student', { uuid: '{{ $student->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $student->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Students') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by adding a new student.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-student" title="{{ __('Create Student') }}" size="3xl">
        <livewire:pages::app.students.create />
    </x-slide>

    <x-slide id="edit-student" title="{{ __('Edit Student') }}" size="3xl">
        <livewire:pages::app.students.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>

