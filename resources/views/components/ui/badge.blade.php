@props([
    'status' => null,
    'variant' => 'default',
])

@php
// Status-based variants (for order statuses)
$statusClasses = [
    'pending' => 'bg-warning-100 text-warning-800 border-warning-300',
    'confirmed' => 'bg-success-100 text-success-800 border-success-300',
];

// Generic variants
$variantClasses = [
    'default' => 'bg-secondary-100 text-secondary-800 border-secondary-300',
    'primary' => 'bg-primary-100 text-primary-800 border-primary-300',
    'success' => 'bg-success-100 text-success-800 border-success-300',
    'danger' => 'bg-danger-100 text-danger-800 border-danger-300',
    'warning' => 'bg-warning-100 text-warning-800 border-warning-300',
    'info' => 'bg-info-100 text-info-800 border-info-300',
];

// Determine which class to use
if ($status) {
    $classes = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border ' . ($statusClasses[$status] ?? $variantClasses['default']);
} else {
    $classes = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border ' . ($variantClasses[$variant] ?? $variantClasses['default']);
}
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
