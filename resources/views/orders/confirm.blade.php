@extends('layouts.app')

@section('title', '- ยืนยันออเดอร์ ' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-ui.card class="mb-6 fade-in">
        <h2 class="text-2xl font-bold mb-6 text-secondary-900">ยืนยันออเดอร์: {{ $order->order_number }}</h2>
        
        <!-- Order Summary -->
        <div class="bg-secondary-50 p-6 rounded-lg mb-6">
            <h3 class="font-bold text-lg mb-4 text-secondary-900">📦 สรุปรายการสินค้า</h3>
            
            <div class="space-y-3 mb-4">
                @foreach($order->orderDetails as $detail)
                    <div class="flex justify-between items-center py-3 border-b border-secondary-200 last:border-0">
                        <div class="flex-1">
                            <div class="font-semibold text-secondary-900">{{ $detail->product->product_name }}</div>
                            <div class="text-sm text-secondary-600">จำนวน: {{ $detail->quantity }} × ฿{{ number_format($detail->unit_price, 2) }}</div>
                        </div>
                        <div class="font-semibold text-secondary-700 text-lg">
                            ฿{{ number_format($detail->subtotal, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="flex justify-between items-center pt-4 border-t-2 border-secondary-300">
                <span class="text-xl font-bold text-secondary-900">ยอดรวมทั้งหมด:</span>
                <span class="text-3xl font-bold text-success-600">
                    ฿{{ number_format($order->total_amount, 2) }}
                </span>
            </div>
        </div>
        
        <!-- Shipping Address Form -->
        <form method="POST" action="{{ route('orders.confirm', $order->order_number) }}"
              x-data="{ loading: false }" 
              x-on:submit="loading = true">
            @csrf
            
            <x-form.textarea 
                name="shipping_address" 
                label="ที่อยู่จัดส่ง"
                rows="5"
                required
                placeholder="กรุณากรอกที่อยู่จัดส่ง เช่น&#10;123 หมู่ 5 ถนน ABC&#10;ตำบล XYZ อำเภอ DEF&#10;จังหวัด GHI 12345&#10;เบอร์โทร: 0812345678"
                class="mb-2"
            />
            
            <p class="text-sm text-secondary-500 mb-6">
                📍 กรุณากรอกที่อยู่จัดส่งให้ครบถ้วน เพื่อความสะดวกในการจัดส่งสินค้า
            </p>
            
            <x-ui.alert type="warning" class="mb-6">
                <h4 class="font-bold mb-1">⚠️ หมายเหตุ</h4>
                <p class="text-sm">
                    เมื่อยืนยันออเดอร์แล้ว คุณจะไม่สามารถแก้ไขรายการสินค้าได้อีก กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนยืนยัน
                </p>
            </x-ui.alert>

            <div class="flex flex-col md:flex-row justify-end gap-4">
                <x-ui.button href="{{ route('orders.show', $order->order_number) }}" variant="secondary">
                    ยกเลิก
                </x-ui.button>
                <x-ui.button type="submit" variant="success" size="lg" x-bind:loading="loading">
                    ✓ ยืนยันออเดอร์
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
