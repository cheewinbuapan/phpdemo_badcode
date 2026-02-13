@props([
    'icon' => '📦',
    'title' => 'ไม่พบข้อมูล',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    <div class="text-6xl mb-4">{{ $icon }}</div>
    <h3 class="text-xl font-semibold text-secondary-700 mb-2">{{ $title }}</h3>
    @if($description)
        <p class="text-secondary-500 mb-6">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
