@props([
    'variant' => 'primary',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'size' => 'md',
])

@php
$baseClasses = 'btn-base inline-flex items-center justify-center font-semibold transition-smooth disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = [
    'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    'secondary' => 'bg-secondary-300 text-secondary-800 hover:bg-secondary-400 focus:ring-secondary-500',
    'success' => 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500',
    'danger' => 'bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500',
    'warning' => 'bg-warning-500 text-white hover:bg-warning-600 focus:ring-warning-500',
    'info' => 'bg-info-600 text-white hover:bg-info-700 focus:ring-info-500',
    'outline' => 'border-2 border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
];

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-6 py-2 text-base',
    'lg' => 'px-8 py-3 text-lg',
];

$classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => $classes]) }}
    @if($loading || $disabled) disabled @endif
>
    @if($loading)
        <svg class="spinner w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        </svg>
    @endif
    
    {{ $slot }}
</button>
