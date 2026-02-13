@extends('layouts.app')

@section('title', '- รายละเอียดออเดอร์ ' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto">
    <x-ui.card class="mb-6 fade-in">
        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold mb-3 text-secondary-900">{{ $order->order_number }}</h2>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.badge :status="$order->status_id === \App\Models\ProductStatus::PENDING ? 'pending' : 'confirmed'">
                        {{ $order->status->status_name }}
                    </x-ui.badge>
                    <span class="text-secondary-600 text-sm">
                        📅 สร้างเมื่อ: {{ $order->created_at->format('d/m/Y H:i') }} น.
                    </span>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                @can('update', $order)
                    <x-ui.button href="{{ route('orders.edit', $order->order_number) }}" variant="primary">
                        ✏️ แก้ไขออเดอร์
                    </x-ui.button>
                @endcan
                
                @can('confirm', $order)
                    @if(!$order->shipping_address)
                        <x-ui.button href="{{ route('orders.confirm.form', $order->order_number) }}" variant="success">
                            ✓ ยืนยันออเดอร์
                        </x-ui.button>
                    @endif
                @endcan
            </div>
        </div>

        <div class="border-t border-secondary-200 pt-6">
            <h3 class="font-bold text-lg mb-4 text-secondary-900">📦 รายการสินค้า</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-secondary-700">สินค้า</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-secondary-700">ราคาต่อหน่วย</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-secondary-700">จำนวน</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-secondary-700">รวม</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200">
                        @foreach($order->orderDetails as $detail)
                            <tr class="hover:bg-secondary-50 transition-smooth">
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-secondary-900">{{ $detail->product->product_name }}</div>
                                    <div class="text-sm text-secondary-500">{{ตัวอย่าง $detail->product_number }}</div>
                                </td>
                                <td class="px-4 py-4 text-right text-secondary-700">
                                    ฿{{ number_format($detail->unit_price, 2) }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <x-ui.badge variant="primary">{{ $detail->quantity }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-secondary-900">
                                    ฿{{ number_format($detail->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-success-50">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-right font-bold text-lg text-secondary-900">
                                ยอดรวมทั้งหมด:
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-3xl text-success-600">
                                ฿{{ number_format($order->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        @if($order->shipping_address)
            <div class="border-t border-secondary-200 mt-6 pt-6">
                <h3 class="font-bold text-lg mb-3 text-secondary-900">📍 ที่อยู่จัดส่ง</h3>
                <div class="bg-info-50 border border-info-200 p-4 rounded-lg">
                    <p class="text-secondary-700 whitespace-pre-line leading-relaxed">{{ $order->shipping_address }}</p>
                </div>
            </div>
        @endif
    </x-ui.card>

    <div class="text-center">
        <x-ui.button href="{{ route('orders.index') }}" variant="outline">
            ← กลับไปหน้าออเดอร์ทั้งหมด
        </x-ui.button>
    </div>
</div>
@endsection
