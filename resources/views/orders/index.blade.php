@extends('layouts.app')

@section('title', '- ออเดอร์ของฉัน')

@section('content')
<x-ui.card class="fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-secondary-900">ออเดอร์ของฉัน</h2>
        <x-ui.button href="{{ route('orders.create') }}" variant="primary">
            + สร้างออเดอร์ใหม่
        </x-ui.button>
    </div>
    
    @forelse($orders as $order)
        <div class="border border-secondary-200 rounded-lg p-4 md:p-6 mb-4 hover:shadow-md transition-smooth">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <div class="flex-1 w-full">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <h3 class="text-lg font-bold text-primary-600">
                            <a href="{{ route('orders.show', $order->order_number) }}" class="hover:underline transition-smooth">
                                {{ $order->order_number }}
                            </a>
                        </h3>
                        <x-ui.badge :status="$order->status_id === \App\Models\ProductStatus::PENDING ? 'pending' : 'confirmed'">
                            {{ $order->status->status_name }}
                        </x-ui.badge>
                    </div>
                    
                    <div class="text-sm text-secondary-600 space-y-1.5">
                        <p>📅 สร้างเมื่อ: {{ $order->created_at->format('d/m/Y H:i') }} น.</p>
                        <p>📦 รายการสินค้า: {{ $order->orderDetails->count() }} รายการ</p>
                        @if($order->shipping_address)
                            <p class="text-secondary-500">
                                <span class="font-semibold">📍 ที่อยู่จัดส่ง:</span> 
                                {{ Str::limit($order->shipping_address, 50) }}
                            </p>
                        @endif
                    </div>
                </div>
                
                <div class="text-left md:text-right w-full md:w-auto">
                    <div class="text-2xl md:text-3xl font-bold text-success-600 mb-4">
                        ฿{{ number_format($order->total_amount, 2) }}
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button 
                            href="{{ route('orders.show', $order->order_number) }}" 
                            variant="secondary"
                            size="sm"
                        >
                            ดูรายละเอียด
                        </x-ui.button>
                        
                        @if($order->status_id === \App\Models\ProductStatus::PENDING)
                            <x-ui.button 
                                href="{{ route('orders.edit', $order->order_number) }}" 
                                variant="primary"
                                size="sm"
                            >
                                แก้ไข
                            </x-ui.button>
                            
                            @if(!$order->shipping_address)
                                <x-ui.button 
                                    href="{{ route('orders.confirm.form', $order->order_number) }}" 
                                    variant="success"
                                    size="sm"
                                >
                                    ยืนยัน
                                </x-ui.button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <x-ui.empty-state 
            icon="📦" 
            title="ยังไม่มีออเดอร์"
            description="เริ่มต้นสร้างออเดอร์แรกของคุณได้เลย"
        >
            <x-ui.button href="{{ route('orders.create') }}" variant="primary" size="lg">
                สร้างออเดอร์ใหม่
            </x-ui.button>
        </x-ui.empty-state>
    @endforelse
</x-ui.card>
@endsection
