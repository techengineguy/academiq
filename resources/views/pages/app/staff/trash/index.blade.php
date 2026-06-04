<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Teacher;
use App\Models\Staff;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Staff Trash')] 
class extends Component {
    use WithPagination;
    use Interactions;

    public string $activeTab = 'teachers';
    public $itemIdToDelete = null;
    public $itemTypeToDelete = null;

    #[Computed]
    public function trashedTeachers()
    {
        return Teacher::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function trashedStaff()
    {
        return Staff::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);
    }

    public function restoreTeacher($id): void
    {
        $teacher = Teacher::onlyTrashed()
            ->findOrFail($id);
        
        $teacher->restore();
        unset($this->trashedTeachers);
        
        Flux::toast(variant: 'success', text: __('Teacher restored successfully.'));
    }

    public function restoreStaff($id): void
    {
        $staff = Staff::onlyTrashed()
            ->findOrFail($id);
        
        $staff->restore();
        unset($this->trashedStaff);
        
        Flux::toast(variant: 'success', text: __('Staff member restored successfully.'));
    }

    public function confirmPermanentDelete($id, $type): void
    {
        $this->itemIdToDelete = $id;
        $this->itemTypeToDelete = $type;

        $itemNames = [
            'teacher' => 'Teacher',
            'staff' => 'Staff Member',
        ];
        $itemName = $itemNames[$type] ?? 'Item';
        
        $this->dialog()
            ->question(__('Permanently delete this ' . strtolower($itemName) . '? This action cannot be undone.'))
            ->confirm(__('Delete'), method: 'permanentlyDelete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function permanentlyDelete(): void
    {
        if (! $this->itemIdToDelete || ! $this->itemTypeToDelete) return;

        if ($this->itemTypeToDelete === 'teacher') {
            Teacher::onlyTrashed()
                ->findOrFail($this->itemIdToDelete)
                ->forceDelete();
            unset($this->trashedTeachers);
            Flux::toast(variant: 'success', text: __('Teacher permanently deleted.'));
        } elseif ($this->itemTypeToDelete === 'staff') {
            Staff::onlyTrashed()
                ->findOrFail($this->itemIdToDelete)
                ->forceDelete();
            unset($this->trashedStaff);
            Flux::toast(variant: 'success', text: __('Staff member permanently deleted.'));
        }

        $this->itemIdToDelete = null;
        $this->itemTypeToDelete = null;
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Staff Trash') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage deleted records.') }}</p>
        </div>
    </div>

    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="teachers" icon="trash">
                {{ __('Teachers') }}
                @if($this->trashedTeachers->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->trashedTeachers->total() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="staff" icon="trash">
                {{ __('Staff Members') }}
                @if($this->trashedStaff->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->trashedStaff->total() }}</flux:badge>
                @endif
            </flux:tab>
        </flux:tabs>

        <flux:tab.panel name="teachers">
            <flux:card>
                @if($this->trashedTeachers->count())
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
                                        {{ $teacher->user?->first_name }} {{ $teacher->user?->last_name }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $teacher->user?->email }}</flux:table.cell>
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
                                                wire:click="confirmPermanentDelete({{ $teacher->id }}, 'teacher')"
                                                title="{{ __('Permanently Delete') }}"
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
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Teachers') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('All your teachers are safe.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="staff">
            <flux:card>
                @if($this->trashedStaff->count())
                    <flux:table :paginate="$this->trashedStaff">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Email') }}</flux:table.column>
                            <flux:table.column>{{ __('Employee ID') }}</flux:table.column>
                            <flux:table.column>{{ __('Designation') }}</flux:table.column>
                            <flux:table.column>{{ __('Deleted') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->trashedStaff as $staff)
                            <flux:table.rows>
                                <flux:table.row :key="$staff->id">
                                    <flux:table.cell>
                                        {{ $staff->user?->first_name }} {{ $staff->user?->last_name }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $staff->user?->email }}</flux:table.cell>
                                    <flux:table.cell>{{ $staff->employee_id }}</flux:table.cell>
                                    <flux:table.cell>{{ $staff->designation }}</flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-sm text-gray-500">
                                            {{ $staff->deleted_at?->format('M d, Y H:i') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                size="sm" 
                                                variant="primary" 
                                                icon="arrow-uturn-left"
                                                wire:click="restoreStaff({{ $staff->id }})"
                                                title="{{ __('Restore') }}"
                                            />
                                            <flux:button 
                                                size="sm" 
                                                variant="danger" 
                                                icon="trash"
                                                wire:click="confirmPermanentDelete({{ $staff->id }}, 'staff')"
                                                title="{{ __('Permanently Delete') }}"
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
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Staff Members') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('All your staff members are safe.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>
    </flux:tab.group>
</div>