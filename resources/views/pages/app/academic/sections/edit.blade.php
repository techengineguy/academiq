<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Section;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public ?Section $section = null;

    public string $name = '';
    public string $class_id = '';
    public string $capacity = '';
    public string $class_teacher_id = '';
    public string $description = '';
    public string $status = 'active';

    #[On('edit-section')]
    public function loadSection(string $uuid): void
    {
        $this->section = Section::where('uuid', $uuid)->firstOrFail();

        $this->name = $this->section->name;
        $this->class_id = $this->section->class_id;
        $this->capacity = $this->section->capacity;
        $this->class_teacher_id = $this->section->class_teacher_id ?? '';
        $this->description = $this->section->description;
        $this->status = $this->section->status;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:999'],
            'class_teacher_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->section->update([
            'name' => $this->name,
            'class_id' => $this->class_id,
            'capacity' => $this->capacity,
            'class_teacher_id' => $this->class_teacher_id ?: null,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Section updated successfully.'));

        $this->redirect(route('sections.index'), navigate: true);
    }
};
?>

<div>
    @if($this->section)
        <form wire:submit="update" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Name') }}" wire:model="name" required />
                <flux:select label="{{ __('Class') }}" variant="listbox" wire:model="class_id" required>
                    <flux:select.option value="">{{ __('Select Class') }}</flux:select.option>
                    @forelse(ClassModel::get() as $class)
                        <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                    @empty
                        <flux:select.option value="">{{ __('No Classes Available') }}</flux:select.option>
                    @endforelse
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input label="{{ __('Capacity') }}" type="number" wire:model="capacity" required />
                <flux:select label="{{ __('Class Teacher') }}" variant="listbox" wire:model="class_teacher_id" searchable>
                    <flux:select.option value="">{{ __('Select Teacher') }}</flux:select.option>
                    @forelse(App\Models\User::where('role', 'teacher')->get() as $teacher)
                        <flux:select.option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</flux:select.option>
                    @empty
                        <flux:select.option value="">{{ __('No Teachers Available') }}</flux:select.option>
                    @endforelse
                </flux:select>
            </div>

            <flux:textarea label="{{ __('Description') }}" wire:model="description" />

            <flux:select label="{{ __('Status') }}" variant="listbox" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-section')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>