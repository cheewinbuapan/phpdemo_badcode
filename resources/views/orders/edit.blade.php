@extends('layouts.app')

@section('title', '- แก้ไขออเดอร์ ' . $order->order_number)

@section('content')
<x-ui.card class="fade-in">
    <h2 class="text-2xl font-bold mb-6 text-secondary-900">แก้ไขออเดอร์: {{ $order->order_number }}</h2>
    
    <form method="POST" action="{{ route('orders.update', $order->order_number) }}" id="orderForm"
          x-data="{ loading: false }" 
          x-on:submit="loading = true">
        @csrf
        @method('PUT')
        
        <div class="mb-6">
            <h3 class="text-lg font-bold mb-4 text-secondary-800">เลือกสินค้า</h3>
            
            <div class="space-y-3">
                @foreach($products as $product)
                    @php
                        // Check if this product is in the current order
                        $orderDetail = $order->orderDetails->firstWhere('product_id', $product->product_id);
                        $isSelected = $orderDetail !== null;
                        $currentQuantity = $isSelected ? $orderDetail->quantity : 1;
                    @endphp
                    
                    <div class="border border-secondary-200 rounded-lg p-4 hover:bg-secondary-50 transition-smooth">
                        <label class="flex flex-col md:flex-row items-start md:items-center gap-4 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="products[{{ $loop->index }}][selected]" 
                                value="1"
                                class="product-checkbox w-5 h-5 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                                data-index="{{ $loop->index }}"
                                {{ $isSelected ? 'checked' : '' }}
                            >
                            <div class="flex-1">
                                <div class="font-bold text-secondary-900">{{ $product->product_name }}</div>
                                <div class="text-sm text-secondary-500">{{ตัวอย่าง $product->product_number }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-success-600 font-bold text-lg">
                                    ฿{{ number_format($product->price, 2) }}
                                </div>
                            </div>
                            <div>
                                <input 
                                    type="number" 
                                    name="products[{{ $loop->index }}][quantity]" 
                                    class="quantity-input w-20 px-3 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:border-primary-500"
                                    min="1"
                                    value="{{ $currentQuantity }}"
                                    {{ $isSelected ? '' : 'disabled' }}
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

        <div class="border-t border-secondary-200 pt-6">
            <div class="bg-info-50 border border-info-200 p-4 rounded-lg mb-6">
                <h4 class="font-bold mb-2 text-info-900">📊 สรุปออเดอร์ปัจจุบัน</h4>
                <div class="text-sm text-info-800 space-y-1">
                    <p>จำนวนรายการ: {{ $order->orderDetails->count() }} รายการ</p>
                    <p class="text-lg font-bold text-success-600">
                        ยอดรวม: ฿{{ number_format($order->total_amount, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-end gap-4">
            <x-ui.button href="{{ route('orders.show', $order->order_number) }}" variant="secondary">
                ยกเลิก
            </x-ui.button>
            <x-ui.button type="submit" variant="primary" x-bind:loading="loading">
                💾 บันทึกการแก้ไข
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
