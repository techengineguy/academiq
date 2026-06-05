<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use App\Models\LessonPlan;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new #[Title('Trash')] 
class extends Component {
    use WithPagination;
    use Interactions;

    public string $activeTab = 'subjects';
    public $itemIdToDelete = null;
    public $itemTypeToDelete = null;

    #[Computed]
    public function trashedSubjects()
    {
        return Subject::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
    }

    #[Computed]
    public function trashedClasses()
    {
        return ClassModel::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
    }

    #[Computed]
    public function trashedAcademicYears()
    {
        return AcademicYear::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
    }

    #[Computed]
    public function trashedLessonPlans()
    {
        return LessonPlan::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
    }

    public function restoreSubject($id): void
    {
        $subject = Subject::onlyTrashed()->findOrFail($id);
        $subject->restore();
        
        unset($this->trashedSubjects);
        Flux::toast(variant: 'success', text: __('Subject restored successfully.'));
    }

    public function restoreClass($id): void
    {
        $class = ClassModel::onlyTrashed()->findOrFail($id);
        $class->restore();
        
        unset($this->trashedClasses);
        Flux::toast(variant: 'success', text: __('Class restored successfully.'));
    }

    public function restoreAcademicYear($id): void
    {
        $year = AcademicYear::onlyTrashed()->findOrFail($id);
        $year->restore();
        
        unset($this->trashedAcademicYears);
        Flux::toast(variant: 'success', text: __('Academic year restored successfully.'));
    }

    public function restoreLessonPlan($id): void
    {
        $plan = LessonPlan::onlyTrashed()->findOrFail($id);
        $plan->restore();
        
        unset($this->trashedLessonPlans);
        Flux::toast(variant: 'success', text: __('Lesson plan restored successfully.'));
    }

    public function confirmPermanentDelete($id, $type): void
    {
        $this->itemIdToDelete = $id;
        $this->itemTypeToDelete = $type;

        $itemNames = [
            'subject' => 'Subject',
            'class' => 'Class',
            'academic-year' => 'Academic Year',
            'lesson-plan' => 'Lesson Plan',
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

        if ($this->itemTypeToDelete === 'subject') {
            Subject::onlyTrashed()->findOrFail($this->itemIdToDelete)->forceDelete();
            unset($this->trashedSubjects);
            Flux::toast(variant: 'success', text: __('Subject permanently deleted.'));
        } elseif ($this->itemTypeToDelete === 'class') {
            ClassModel::onlyTrashed()->findOrFail($this->itemIdToDelete)->forceDelete();
            unset($this->trashedClasses);
            Flux::toast(variant: 'success', text: __('Class permanently deleted.'));
        } elseif ($this->itemTypeToDelete === 'academic-year') {
            AcademicYear::onlyTrashed()->findOrFail($this->itemIdToDelete)->forceDelete();
            unset($this->trashedAcademicYears);
            Flux::toast(variant: 'success', text: __('Academic year permanently deleted.'));
        } elseif ($this->itemTypeToDelete === 'lesson-plan') {
            LessonPlan::onlyTrashed()->findOrFail($this->itemIdToDelete)->forceDelete();
            unset($this->trashedLessonPlans);
            Flux::toast(variant: 'success', text: __('Lesson plan permanently deleted.'));
        }

        $this->itemIdToDelete = null;
        $this->itemTypeToDelete = null;
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Trash') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage deleted records.') }}</p>
        </div>
    </div>

    <flux:tab.group>
        <flux:tabs>
            <flux:tab name="academic-years" icon="trash">
                {{ __('Academic Years') }}
                @if($this->trashedAcademicYears->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->trashedAcademicYears->total() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="subjects" icon="trash">
                {{ __('Subjects') }}
                @if($this->trashedSubjects->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->trashedSubjects->total() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="classes" icon="trash">
                {{ __('Classes') }}
                @if($this->trashedClasses->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->trashedClasses->total() }}</flux:badge>
                @endif
            </flux:tab>
            <flux:tab name="lesson-plans" icon="trash">
                {{ __('Lesson Plans') }}
                @if($this->trashedLessonPlans->total() > 0)
                    <flux:badge variant="info" class="ml-2">{{ $this->trashedLessonPlans->total() }}</flux:badge>
                @endif
            </flux:tab>
        </flux:tabs>

        <flux:tab.panel name="subjects">
            <flux:card>
                @if($this->trashedSubjects->count())
                    <flux:table :paginate="$this->trashedSubjects">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Code') }}</flux:table.column>
                            <flux:table.column>{{ __('Type') }}</flux:table.column>
                            <flux:table.column>{{ __('Deleted') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->trashedSubjects as $subject)
                            <flux:table.rows>
                                <flux:table.row :key="$subject->id">
                                    <flux:table.cell>{{ $subject->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $subject->code }}</flux:table.cell>
                                    <flux:table.cell>{{ ucfirst($subject->type) }}</flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-sm text-gray-500">
                                            {{ $subject->deleted_at->format('M d, Y H:i') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                size="sm" 
                                                variant="primary" 
                                                icon="arrow-uturn-left"
                                                wire:click="restoreSubject({{ $subject->id }})"
                                                title="{{ __('Restore') }}"
                                            />
                                            <flux:button 
                                                size="sm" 
                                                variant="danger" 
                                                icon="trash"
                                                wire:click="confirmPermanentDelete({{ $subject->id }}, 'subject')"
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
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Subjects') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('All your subjects are safe.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="classes">
            <flux:card>
                @if($this->trashedClasses->count())
                    <flux:table :paginate="$this->trashedClasses">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Code') }}</flux:table.column>
                            <flux:table.column>{{ __('Capacity') }}</flux:table.column>
                            <flux:table.column>{{ __('Deleted') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->trashedClasses as $class)
                            <flux:table.rows>
                                <flux:table.row :key="$class->id">
                                    <flux:table.cell>{{ $class->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $class->code }}</flux:table.cell>
                                    <flux:table.cell>{{ $class->capacity }}</flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-sm text-gray-500">
                                            {{ $class->deleted_at->format('M d, Y H:i') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                size="sm" 
                                                variant="primary" 
                                                icon="arrow-uturn-left"
                                                wire:click="restoreClass({{ $class->id }})"
                                                title="{{ __('Restore') }}"
                                            />
                                            <flux:button 
                                                size="sm" 
                                                variant="danger" 
                                                icon="trash"
                                                wire:click="confirmPermanentDelete({{ $class->id }}, 'class')"
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
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Classes') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('All your classes are safe.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="academic-years">
            <flux:card>
                @if($this->trashedAcademicYears->count())
                    <flux:table :paginate="$this->trashedAcademicYears">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                            <flux:table.column>{{ __('End Date') }}</flux:table.column>
                            <flux:table.column>{{ __('Deleted') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->trashedAcademicYears as $year)
                            <flux:table.rows>
                                <flux:table.row :key="$year->id">
                                    <flux:table.cell>{{ $year->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $year->start_date->format('M d, Y') }}</flux:table.cell>
                                    <flux:table.cell>{{ $year->end_date->format('M d, Y') }}</flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-sm text-gray-500">
                                            {{ $year->deleted_at->format('M d, Y H:i') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                size="sm" 
                                                variant="primary" 
                                                icon="arrow-uturn-left"
                                                wire:click="restoreAcademicYear({{ $year->id }})"
                                                title="{{ __('Restore') }}"
                                            />
                                            <flux:button 
                                                size="sm" 
                                                variant="danger" 
                                                icon="trash"
                                                wire:click="confirmPermanentDelete({{ $year->id }}, 'academic-year')"
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
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Academic Years') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('All your academic years are safe.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>

        <flux:tab.panel name="lesson-plans">
            <flux:card>
                @if($this->trashedLessonPlans->count())
                    <flux:table :paginate="$this->trashedLessonPlans">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Date') }}</flux:table.column>
                            <flux:table.column>{{ __('Topic') }}</flux:table.column>
                            <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                            <flux:table.column>{{ __('Class') }}</flux:table.column>
                            <flux:table.column>{{ __('Deleted') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        @foreach($this->trashedLessonPlans as $plan)
                            <flux:table.rows>
                                <flux:table.row :key="$plan->id">
                                    <flux:table.cell>{{ $plan->lesson_date?->format('M d, Y') }}</flux:table.cell>
                                    <flux:table.cell>{{ Str::limit($plan->topic, 30) }}</flux:table.cell>
                                    <flux:table.cell>{{ $plan->teacher?->first_name }} {{ $plan->teacher?->last_name }}</flux:table.cell>
                                    <flux:table.cell>{{ $plan->class?->name }}</flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-sm text-gray-500">
                                            {{ $plan->deleted_at->format('M d, Y H:i') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                size="sm" 
                                                variant="primary" 
                                                icon="arrow-uturn-left"
                                                wire:click="restoreLessonPlan({{ $plan->id }})"
                                                title="{{ __('Restore') }}"
                                            />
                                            <flux:button 
                                                size="sm" 
                                                variant="danger" 
                                                icon="trash"
                                                wire:click="confirmPermanentDelete({{ $plan->id }}, 'lesson-plan')"
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
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Deleted Lesson Plans') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('All your lesson plans are safe.') }}</p>
                    </div>
                @endif
            </flux:card>
        </flux:tab.panel>
    </flux:tab.group>
</div>
