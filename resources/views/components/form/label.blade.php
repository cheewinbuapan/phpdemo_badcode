@props([
    'for' => null,
    'required' => false,
])

<label 
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-semibold text-secondary-700 mb-2']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-danger-500">*</span>
    @endif
</label>
