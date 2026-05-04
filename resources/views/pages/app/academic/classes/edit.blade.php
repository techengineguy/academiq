<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    public ?ClassModel $class = null;

    public string $name = '';
    public string $code = '';
    public string $academic_year_id = '';
    public string $capacity = '';
    public string $status = 'active';

    #[On('edit-class')]
    public function loadClass(string $uuid): void
    {
        $this->class = ClassModel::where('tenant_id', Auth::user()->tenant_id)
            ->where('uuid', $uuid)->firstOrFail();

        $this->name = $this->class->name;
        $this->code = $this->class->code;
        $this->academic_year_id = $this->class->academic_year_id;
        $this->capacity = $this->class->capacity;
        $this->status = $this->class->status;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->class->update([
            'name' => $this->name,
            'code' => $this->code,
            'academic_year_id' => $this->academic_year_id,
            'capacity' => $this->capacity,
            'status' => $this->status,
        ]);

        Flux::toast(variant: 'success', text: __('Class updated successfully.'));

        $this->redirect(route('classes.index'), navigate: true);
    }
};
?>

<div>
    @if($this->class)
        <form wire:submit="update" class="space-y-6">
            <flux:input
                label="{{ __('Name') }}"
                wire:model="name"
                required
            />
            <flux:input
                label="{{ __('Code') }}"
                wire:model="code"
                required
            />
            <flux:select label="{{ __('Academic Year') }}" wire:model="academic_year_id" required>
                <option value="">{{ __('Select Academic Year') }}</option>
                @forelse(AcademicYear::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->get() as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @empty
                @endforelse
            </flux:select>
            <flux:input
                label="{{ __('Capacity') }}"
                wire:model="capacity"
                type="number"
                min="1"
                required
            />
            <flux:select label="{{ __('Status') }}" wire:model="status" required>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
            <div class="flex gap-3 pt-2">
                <flux:button type="submit" class="button" variant="primary">{{ __('Update') }}</flux:button>
                <flux:button x-on:click="$tsui.close.slide('edit-class')" variant="subtle">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-center h-32 text-zinc-400 text-sm">
            {{ __('Loading...') }}
        </div>
    @endif
</div>


