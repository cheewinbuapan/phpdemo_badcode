@props([
    'title' => null,
    'padding' => true,
])

@php
$classes = 'card-base ' . ($padding ? 'p-6' : '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || isset($header))
        <div class="mb-4 pb-4 border-b border-secondary-200">
            @isset($header)
                {{ $header }}
            @else
                <h3 class="text-xl font-bold text-secondary-900">{{ $title }}</h3>
            @endisset
        </div>
    @endif
    
    <div>
        {{ $slot }}
    </div>
    
    @isset($footer)
        <div class="mt-4 pt-4 border-t border-secondary-200">
            {{ $footer }}
        </div>
    @endisset
</div>
