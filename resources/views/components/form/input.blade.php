@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'error' => null,
])

@php
$errorClass = $error ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-200' : '';
$classes = 'input-base ' . $errorClass;
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <x-form.label :for="$name" :required="$required">{{ $label }}</x-form.label>
    @endif
    
    <input 
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->except(['class', 'label'])->merge(['class' => $classes]) }}
    >
    
    @if($error || $errors->has($name))
        <p class="mt-1 text-sm text-danger-600">
            {{ $error ?? $errors->first($name) }}
        </p>
    @endif
</div>
