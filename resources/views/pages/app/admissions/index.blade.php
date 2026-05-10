
<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\AdmissionApplication;
use Livewire\WithPagination;
use Flux\Flux;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Auth;

new #[Title('Admissions')]
class extends Component {
    use WithPagination, Interactions;

    #[Computed]
    public function applications()
    {
        return AdmissionApplication::where('tenant_id', Auth::user()->tenant_id)
            ->with(['class','academicYear','reviewedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public $applicationIdToDelete = null;

    public function confirmDelete($id): void
    {
        $this->applicationIdToDelete = $id;

        $this->dialog()
            ->question(__('Are you sure you want to delete this application?'))
            ->confirm(__('Delete'), method: 'delete')
            ->cancel(__('Cancel'))
            ->send();
    }

    #[On('confirm')]
    public function delete(): void
    {
        if (! $this->applicationIdToDelete) return;

        AdmissionApplication::where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($this->applicationIdToDelete)->delete();

        $this->applicationIdToDelete = null;
        unset($this->applications);

        Flux::toast(variant: 'success', text: __('Application deleted.'));
    }
};
?>

<div class="py-4 space-y-6">
    <x-dialog/>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Admissions') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage admissions and incoming applications.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button class="button" href="{{ route('admissions.apply', ['institution' => auth()->user()->institution->uuid]) }}" icon="share" target="_blank">
                {{ __('Share Public Link') }}
            </flux:button>
            <flux:button class="button" x-on:click="$tsui.open.slide('create-admission')" icon="plus">
                {{ __('New Application') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        @if($this->applications->count())
            <flux:table :paginate="$this->applications">
                <flux:table.columns>
                    <flux:table.column>{{ __('Application #') }}</flux:table.column>
                    <flux:table.column>{{ __('Applicant') }}</flux:table.column>
                    <flux:table.column>{{ __('Class') }}</flux:table.column>
                    <flux:table.column>{{ __('Parent Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Applied On') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                @foreach($this->applications as $app)
                    <flux:table.rows>
                        <flux:table.row :key="$app->id">
                            <flux:table.cell>{{ $app->application_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $app->student_name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $app->class?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $app->parent_email ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ optional($app->application_date)->format('Y-m-d') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="in_array($app->status, ['approved', 'admitted']) ? 'green' : ($app->status == 'rejected' ? 'red' : ($app->status == 'under_review' ? 'yellow' : 'gray'))">
                                    {{ str($app->status)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="subtle" 
                                        x-on:click="$tsui.open.slide('edit-admission'), $wire.dispatch('edit-admission', { uuid: '{{ $app->uuid }}' })" 
                                        icon="square-pen" 
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="danger" 
                                        icon="trash"
                                        wire:click="confirmDelete({{ $app->id }})"
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
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('No Applications') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('No admission applications yet; invite applicants or add one manually.') }}</p>
            </div>
        @endif
    </flux:card>

    <x-slide id="create-admission" title="{{ __('Create Application') }}" size="3xl">
        <livewire:pages::app.admissions.create />
    </x-slide>

    <x-slide id="edit-admission" title="{{ __('Edit Application') }}" size="3xl">
        <livewire:pages::app.admissions.edit :uuid="$slideData['uuid'] ?? null" />
    </x-slide>
</div>

