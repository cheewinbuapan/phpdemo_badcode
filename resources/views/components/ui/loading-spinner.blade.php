@props([
    'size' => 'md',
    'color' => 'primary',
])

@php
$sizeClasses = [
    'sm' => 'w-4 h-4',
    'md' => 'w-8 h-8',
    'lg' => 'w-12 h-12',
    'xl' => 'w-16 h-16',
];

$colorClasses = [
    'primary' => 'text-primary-600',
    'white' => 'text-white',
    'secondary' => 'text-secondary-600',
];

$classes = 'spinner inline-block ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($colorClasses[$color] ?? $colorClasses['primary']);
@endphp

<svg {{ $attributes->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
</svg>
