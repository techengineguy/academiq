<?php

use Livewire\Component;
use App\Models\Section;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;
use TallStackUi\Traits\Interactions;

new class extends Component {
    use Interactions;

    public $name = '';
    public $class_id = '';
    public $capacity = '';
    public $class_teacher_id = '';
    public $description = '';
    public $status = 'active';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'required|integer|min:1|max:999',
            'class_teacher_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        Section::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'class_id' => $validated['class_id'],
            'capacity' => $validated['capacity'],
            'class_teacher_id' => $validated['class_teacher_id'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        Flux::toast(variant: 'success', text: __('Section created successfully.'));

        $this->redirect(route('sections.index'), navigate: true);
    }
};
?>

<div>
    <x-dialog/>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Name') }}" placeholder="{{ __('e.g., Section A') }}" wire:model="name" required />
            <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id" required>
                <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                @forelse(ClassModel::where('tenant_id', Auth::user()->tenant_id)->get() as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input label="{{ __('Capacity') }}" type="number" placeholder="{{ __('Enter capacity') }}" wire:model="capacity" required />
            <flux:select label="{{ __('Class Teacher') }}" variant="listbox" wire:model="class_teacher_id">
                <flux:select.option value="">{{ __('Select Teacher') }}</flux:select.option>
                @forelse(App\Models\User::where('tenant_id', Auth::user()->tenant_id)->where('role', 'teacher')->get() as $teacher)
                    <flux:select.option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:select.option>
                @empty
                    <flux:select.option value="">{{ __('No Teachers Available') }}</flux:select.option>
                @endforelse
            </flux:select>
        </div>

        <flux:textarea label="{{ __('Description') }}" placeholder="{{ __('Enter section description') }}" wire:model="description" />

        <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
        </flux:select>

        <div class="flex gap-3">
            <flux:button type="submit" class="button">{{ __('Create') }}</flux:button>
            <flux:button x-on:click="$tsui.close.slide('create-section')" variant="subtle">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>