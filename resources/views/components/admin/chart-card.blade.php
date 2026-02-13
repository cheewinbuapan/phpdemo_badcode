@props([
    'title',
    'chartId',
])

<x-ui.card {{ $attributes }}>
    <x-slot:header>
        <h3 class="text-lg font-bold text-secondary-900">{{ $title }}</h3>
    </x-slot:header>
    
    <div class="relative" style="height: 300px;">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
    
    @if($slot->isNotEmpty())
        <x-slot:footer>
            {{ $slot }}
        </x-slot:footer>
    @endif
</x-ui.card>
