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

new #[Title('Staff Trash')] 
class extends Component {
    use WithPagination;
    use Interactions;

    public $itemIdToDelete = null;

    #[Computed]
    public function trashedTeachers()
    {
        return Teacher::where('tenant_id', Auth::user()->tenant_id)
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);
    }

    public function restoreTeacher($id): void
    {
        $teacher = Teacher::where('tenant_id', Auth::user()->tenant_id)
            ->onlyTrashed()
            ->findOrFail($id);
        
        $teacher->restore();
        unset($this->trashedTeachers);
        
        Flux::toast(variant: 'success', text: __('Teacher restored successfully.'));
    }

    public function confirmPermanentDelete($id): void
    {
        $this->itemIdToDelete = $id;

        $this->dialog()
            ->question(__('Permanently delete this teacher? This action cannot be undone.'))
            ->confirm(__('Delete'), method: 'permanentlyDelete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function permanentlyDelete(): void
    {
        if (! $this->itemIdToDelete) return;

        Teacher::where('tenant_id', Auth::user()->tenant_id)
            ->onlyTrashed()
            ->findOrFail($this->itemIdToDelete)
            ->forceDelete();

        $this->itemIdToDelete = null;
        unset($this->trashedTeachers);
        
        Flux::toast(variant: 'success', text: __('Teacher permanently deleted.'));
    }
};
?>

<div>
    <x-dialog/>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Staff Trash') }}</h1>
            <a href="{{ route('teachers.index') }}" wire:navigate class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                {{ __('Back to Teachers') }}
            </a>
        </div>

        <flux:card>
            @if($this->trashedTeachers->count())
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Total Deleted Teachers:') }} 
                        <span class="font-semibold">{{ $this->trashedTeachers->total() }}</span>
                    </p>
                </div>

                <flux:table :paginate="$this->trashedTeachers">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column>{{ __('Email') }}</flux:table.column>
                        <flux:table.column>{{ __('Employee ID') }}</flux:table.column>
                        <flux:table.column>{{ __('Designation') }}</flux:table.column>
                        <flux:table.column>{{ __('Deleted') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach($this->trashedTeachers as $teacher)
                        <flux:table.rows>
                            <flux:table.row :key="$teacher->id">
                                <flux:table.cell>
                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                </flux:table.cell>
                                <flux:table.cell>{{ $teacher->email }}</flux:table.cell>
                                <flux:table.cell>{{ $teacher->employee_id }}</flux:table.cell>
                                <flux:table.cell>{{ $teacher->designation }}</flux:table.cell>
                                <flux:table.cell>
                                    <span class="text-sm text-gray-500">
                                        {{ $teacher->deleted_at?->format('M d, Y H:i') }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2">
                                        <flux:button 
                                            size="sm" 
                                            variant="primary" 
                                            icon="arrow-uturn-left"
                                            wire:click="restoreTeacher({{ $teacher->id }})"
                                            title="{{ __('Restore') }}"
                                        />
                                        <flux:button 
                                            size="sm" 
                                            variant="danger" 
                                            icon="trash"
                                            wire:click="confirmPermanentDelete({{ $teacher->id }})"
                                            title="{{ __('Permanently Delete') }}"
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        </flux:table.rows>
                    @endforeach
                </flux:table>
            @else
                <div class="p-12 text-center">
                    <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Teachers') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('All your staff members are safe.') }}</p>
                </div>
            @endif
        </flux:card>
    </div>
</div>