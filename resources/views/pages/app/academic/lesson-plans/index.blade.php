<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\LessonPlan;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new #[Title('Lesson Plans')] 
class extends Component {
    use WithPagination;
    use Interactions;

    #[Computed]
    public function lessonPlans()
    {
        return LessonPlan::where('tenant_id', Auth::user()->tenant_id)
            ->with(['teacher', 'class', 'subject'])
            ->orderBy('lesson_date', 'desc')->paginate(10);
    }

    public $lessonPlanIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->lessonPlanIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this lesson plan?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->lessonPlanIdToDelete) return;

        LessonPlan::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->lessonPlanIdToDelete)->delete();

        $this->lessonPlanIdToDelete = null;
        unset($this->lessonPlans);

        Flux::toast(variant: 'success', text: __('Lesson plan deleted successfully.'));
    }
};
?>

<div>
    <x-dialog/>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Lesson Plans') }}</h1>
            <flux:button class="button" x-on:click="$tsui.open.slide('create-lesson-plan')" icon="plus">
                {{ __('New Lesson Plan') }}
            </flux:button>
        </div>

        <flux:card>
            @if($this->lessonPlans->count())
                <flux:table :paginate="$this->lessonPlans">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Topic') }}</flux:table.column>
                        <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                        <flux:table.column>{{ __('Class') }}</flux:table.column>
                        <flux:table.column>{{ __('Subject') }}</flux:table.column>
                        <flux:table.column>{{ __('Attachment') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach($this->lessonPlans as $plan)
                        <flux:table.rows>
                            <flux:table.row :key="$plan->id">
                                <flux:table.cell>{{ $plan->lesson_date?->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>{{ Str::limit($plan->topic, 30) }}</flux:table.cell>
                                <flux:table.cell>{{ $plan->teacher?->first_name }} {{ $plan->teacher?->last_name }}</flux:table.cell>
                                <flux:table.cell>{{ $plan->class?->name }}</flux:table.cell>
                                <flux:table.cell>{{ $plan->subject?->name }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($plan->attachment)
                                        <a href="{{ asset('storage/' . $plan->attachment) }}" download class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                            <flux:icon name="arrow-down-tray" class="h-4 w-4" />
                                            {{ basename($plan->attachment) }}
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2">
                                        <flux:button 
                                            size="sm" 
                                            variant="subtle" 
                                            x-on:click="$tsui.open.slide('edit-lesson-plan'), $wire.dispatch('edit-lesson-plan', { uuid: '{{ $plan->uuid }}' })" 
                                            icon="pencil" 
                                        />
                                        <flux:button 
                                            size="sm" 
                                            variant="danger" 
                                            icon="trash"
                                            wire:click="confirmDelete({{ $plan->id }})"
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
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Lesson Plans') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a new lesson plan.') }}</p>
                </div>
            @endif
        </flux:card>
    </div>

    <x-slide id="create-lesson-plan" title="{{ __('Create Lesson Plan') }}">
        <livewire:pages::app.academic.lesson-plans.create />
    </x-slide>

    <x-slide id="edit-lesson-plan" title="{{ __('Edit Lesson Plan') }}">
        <livewire:pages::app.academic.lesson-plans.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>


