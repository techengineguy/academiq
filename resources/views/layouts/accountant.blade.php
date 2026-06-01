<x-layouts::accountant.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::accountant.sidebar>
