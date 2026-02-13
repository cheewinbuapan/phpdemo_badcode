@props([
    'name',
    'label' => null,
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'rows' => 4,
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
    
    <textarea 
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        {{ $attributes->except(['class', 'label', 'rows'])->merge(['class' => $classes]) }}
    >{{ old($name, $value) }}</textarea>
    
    @if($error || $errors->has($name))
        <p class="mt-1 text-sm text-danger-600">
            {{ $error ?? $errors->first($name) }}
        </p>
    @endif
</div>
