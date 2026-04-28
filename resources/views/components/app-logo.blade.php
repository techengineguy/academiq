@props([
    'sidebar' => false,
])

@if($sidebar)
    <a href="{{ $attributes->get('href') }}" wire:navigate class="items-center justify-center">
        <img src="{{ asset('images/academiq.png') }}" alt="Logo" class="w-46 h-12" />
    </a>
@else
    <a href="{{ $attributes->get('href') }}" wire:navigate class="items-center justify-center">
        <img src="{{ asset('images/academiq.png') }}" alt="Logo" class="w-46 h-12" />
    </a>
@endif
