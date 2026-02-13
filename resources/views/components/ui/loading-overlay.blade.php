@props([
    'show' => false,
    'message' => 'กำลังโหลด...',
])

<div 
    x-data="{ show: @js($show) }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;"
    {{ $attributes }}
>
    <div class="bg-white rounded-lg p-8 shadow-hard max-w-sm w-full mx-4 fade-in">
        <div class="flex flex-col items-center">
            <x-ui.loading-spinner size="xl" />
            <p class="mt-4 text-lg font-semibold text-secondary-700">{{ $message }}</p>
            @if($slot->isNotEmpty())
                <div class="mt-2 text-sm text-secondary-500">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
