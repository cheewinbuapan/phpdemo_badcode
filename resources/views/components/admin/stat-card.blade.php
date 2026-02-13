@props([
    'title',
    'value',
    'icon' => '📊',
    'trend' => null,
    'trendValue' => null,
    'color' => 'primary',
])

@php
$colorClasses = [
    'primary' => 'bg-primary-50 text-primary-600 border-primary-200',
    'success' => 'bg-success-50 text-success-600 border-success-200',
    'warning' => 'bg-warning-50 text-warning-600 border-warning-200',
    'danger' => 'bg-danger-50 text-danger-600 border-danger-200',
    'info' => 'bg-info-50 text-info-600 border-info-200',
];

$trendIcons = [
    'up' => '↑',
    'down' => '↓',
    'neutral' => '→',
];

$trendColors = [
    'up' => 'text-success-600',
    'down' => 'text-danger-600',
    'neutral' => 'text-secondary-600',
];
@endphp

<x-ui.card {{ $attributes->merge(['class' => 'border-l-4 ' . ($colorClasses[$color] ?? $colorClasses['primary'])]) }}>
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <div class="flex items-center mb-2">
                <span class="text-3xl mr-3">{{ $icon }}</span>
                <p class="text-sm font-semibold text-secondary-600 uppercase tracking-wide">{{ $title }}</p>
            </div>
            <p class="text-3xl font-bold text-secondary-900">{{ $value }}</p>
            
            @if($trend && $trendValue)
                <div class="mt-2 flex items-center text-sm">
                    <span class="font-semibold {{ $trendColors[$trend] ?? $trendColors['neutral'] }}">
                        {{ $trendIcons[$trend] ?? '' }} {{ $trendValue }}
                    </span>
                    <span class="ml-2 text-secondary-500">จากเดือนที่แล้ว</span>
                </div>
            @endif
        </div>
    </div>
</x-ui.card>
