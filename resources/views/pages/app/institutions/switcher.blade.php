<?php

use App\Models\Institution;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    #[Computed]
    public function institutions(): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->adminInstitutions;
    }

    public function currentInstitution(): ?Institution
    {
        $id = session('active_institution_id');

        return $id ? Institution::find($id) : null;
    }

    public function switchInstitution(int $institutionId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->adminInstitutions()->where('institutions.id', $institutionId)->exists()) {
            session()->put('active_institution_id', $institutionId);
            $this->redirect(route('dashboard'));
        }
    }
};
?>

<div>
    @if($this->institutions->count() > 1)
        <flux:dropdown>
            <flux:button variant="subtle" size="sm" icon-trailing="chevron-down" class="w-full justify-between truncate">
                {{ $this->currentInstitution()?->name ?? __('Select Institution') }}
            </flux:button>

            <flux:menu>
                @foreach($this->institutions as $institution)
                    <flux:menu.item
                        wire:click="switchInstitution({{ $institution->id }})"
                        :disabled="$this->currentInstitution()?->id === $institution->id"
                    >
                        {{ $institution->name }}
                    </flux:menu.item>
                @endforeach
                <flux:menu.separator />
                <flux:menu.item icon="plus" :href="route('institutions.create')" wire:navigate>
                    {{ __('New Institution') }}
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    @elseif($this->currentInstitution())
        <flux:dropdown>
            <flux:button variant="subtle" size="sm" icon-trailing="chevron-down" class="w-full justify-between truncate">
                {{ $this->currentInstitution()->name }}
            </flux:button>
            <flux:menu>
                <flux:menu.item icon="plus" :href="route('institutions.create')" wire:navigate>
                    {{ __('New Institution') }}
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    @endif
</div>
