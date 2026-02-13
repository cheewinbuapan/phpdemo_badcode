@extends('layouts.app')

@section('title', '- รายการสินค้า')

@section('content')
<x-ui.card class="fade-in">
    <h2 class="text-2xl font-bold mb-6 text-secondary-900">รายการสินค้าทั้งหมด</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <x-ui.card class="hover:scale-105 transition-smooth border border-secondary-200">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-primary-600 mb-2">
                        {{ $product->product_name }}
                    </h3>
                    <p class="text-sm text-secondary-500 mb-3">
                        🏷️ รหัสสินค้า: {{ $product->product_number }}
                    </p>
                    <p class="text-secondary-700 text-sm leading-relaxed">
                        {{ $product->product_description }}
                    </p>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-secondary-200">
                    <span class="text-2xl font-bold text-success-600">
                        ฿{{ number_format($product->price, 2) }}
                    </span>
                    @if($product->stock_quantity > 0)
                        <x-ui.badge variant="success">
                            คงเหลือ: {{ $product->stock_quantity }}
                        </x-ui.badge>
                    @else
                        <x-ui.badge variant="danger">
                            สินค้าหมด
                        </x-ui.badge>
                    @endif
                </div>
            </x-ui.card>
        @empty
            <div class="col-span-3">
                <x-ui.empty-state 
                    icon="🛒" 
                    title="ไม่มีสินค้า"
                    description="ยังไม่มีสินค้าในระบบ"
                />
            </div>
        @endforelse
    </div>

    <div class="mt-8 text-center">
        <x-ui.button href="{{ route('orders.create') }}" variant="primary" size="lg">
            🛍️ สร้างออเดอร์
        </x-ui.button>
    </div>
</x-ui.card>
@endsection
