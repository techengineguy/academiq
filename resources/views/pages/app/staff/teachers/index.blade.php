<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Teacher;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Teachers')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function teachers()
    {
        return Teacher::with('user')
            ->orderBy('created_at', 'desc')->paginate(10);
    }

    public $teacherIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->teacherIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this teacher?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->teacherIdToDelete) return;

        Teacher::findOrFail($this->teacherIdToDelete)->delete();

        $this->teacherIdToDelete = null;
        unset($this->teachers);

        Flux::toast(variant: 'success', text: __('Teacher deleted successfully, Restore from trash.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Teachers') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage teachers and their information.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button class="button" x-on:click="$tsui.open.slide('create-teacher')" icon="plus">
                {{ __('New Teacher') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        @if($this->teachers->count())
            <flux:table :paginate="$this->teachers">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Employee ID') }}</flux:table.column>
                    <flux:table.column>{{ __('Designation') }}</flux:table.column>
                    <flux:table.column>{{ __('Salary') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->teachers as $teacher)
                    <flux:table.rows>
                        <flux:table.row :key="$teacher->id">
                            <flux:table.cell>{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:table.cell>
                            <flux:table.cell>{{ $teacher->email }}</flux:table.cell>
                            <flux:table.cell>{{ $teacher->employee_id }}</flux:table.cell>
                            <flux:table.cell>{{ $teacher->designation }}</flux:table.cell>
                            <flux:table.cell>{{ $teacher->salary !== null ? number_format((float) $teacher->salary, 2) : '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$teacher->status == 'active' ? 'green' : 'gray'">
                                    {{ ucfirst($teacher->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-teacher'), $wire.dispatch('edit-teacher', { uuid: '{{ $teacher->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $teacher->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Teachers') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by adding a new teacher.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-teacher" title="{{ __('Create Teacher') }}" size="xl">
        <livewire:pages::app.staff.teachers.create />
    </x-slide>

    <x-slide id="edit-teacher" title="{{ __('Edit Teacher') }}" size="xl">
        <livewire:pages::app.staff.teachers.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>

