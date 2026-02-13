@extends('layouts.app')

@section('title', '- สร้างออเดอร์')

@section('content')
<x-ui.card class="fade-in">
    <h2 class="text-2xl font-bold mb-6 text-secondary-900">สร้างออเดอร์ใหม่</h2>
    
    <form method="POST" action="{{ route('orders.store') }}" id="orderForm"
          x-data="{ loading: false }" 
          x-on:submit="loading = true">
        @csrf
        
        <div class="mb-6">
            <h3 class="text-lg font-bold mb-4 text-secondary-800">เลือกสินค้า</h3>
            
            <div class="space-y-3">
                @foreach($products as $product)
                    <div class="border border-secondary-200 rounded-lg p-4 hover:bg-secondary-50 transition-smooth">
                        <label class="flex flex-col md:flex-row md:items-center gap-4 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="products[{{ $loop->index }}][selected]" 
                                value="1"
                                class="product-checkbox w-5 h-5 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                                data-index="{{ $loop->index }}"
                            >
                            <div class="flex-1">
                                <div class="font-bold text-secondary-900">{{ $product->product_name }}</div>
                                <div class="text-sm text-secondary-600">{{ $product->product_number }}</div>
                            </div>
                            <div class="text-right md:text-left">
                                <div class="text-success-600 font-bold text-lg">
                                    ฿{{ number_format($product->price, 2) }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-secondary-700">จำนวน:</span>
                                <input 
                                    type="number" 
                                    name="products[{{ $loop->index }}][quantity]" 
                                    class="quantity-input w-20 px-3 py-2 border border-secondary-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-smooth"
                                    min="1"
                                    value="1"
                                    disabled
                                >
                                <input 
                                    type="hidden" 
                                    name="products[{{ $loop->index }}][product_id]" 
                                    value="{{ $product->product_id }}"
                                >
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <x-ui.button href="{{ route('products.index') }}" variant="secondary">
                ยกเลิก
            </x-ui.button>
            <x-ui.button type="submit" variant="primary" x-bind:loading="loading">
                สร้างออเดอร์
            </x-ui.button>
        </div>
    </form>
</x-ui.card>

@push('scripts')
<script>
    // Enable/disable quantity input based on checkbox
    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const index = this.dataset.index;
            const quantityInput = document.querySelector(`input[name="products[${index}][quantity]"]`);
            quantityInput.disabled = !this.checked;
            
            if (!this.checked) {
                quantityInput.value = 1;
            }
        });
    });

    // Submit only selected products
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        // Remove unchecked products from form data
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            if (!checkbox.checked) {
                const index = checkbox.dataset.index;
                const container = checkbox.closest('label').parentElement;
                container.querySelectorAll('input').forEach(input => {
                    input.disabled = true;
                });
            }
        });
    });
</script>
@endpush
@endsection
