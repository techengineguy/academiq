<x-layouts::owner.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::owner.sidebar>
