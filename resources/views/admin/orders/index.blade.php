@extends('layouts.app')

@section('title', '- จัดการออเดอร์ (Admin)')

@section('content')
<x-ui.card class="fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-secondary-900">จัดการออเดอร์ทั้งหมด (Admin)</h2>
        <x-ui.badge variant="warning" class="text-base px-4 py-2">
            👑 ผู้ดูแลระบบ
        </x-ui.badge>
    </div>
    
    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <input 
                type="text" 
                name="search" 
                value="{{ $search ?? '' }}"
                placeholder="ค้นหาด้วยหมายเลขออเดอร์ / ชื่อลูกค้า / อีเมล"
                class="flex-1 input-base"
            >
            <x-ui.button type="submit" variant="primary">
                🔍 ค้นหา
            </x-ui.button>
            @if($search)
                <x-ui.button href="{{ route('admin.orders.index') }}" variant="secondary">
                    ล้างการค้นหา
                </x-ui.button>
            @endif
        </div>
    </form>
    
    <!-- Bulk Confirm Form -->
    <form method="POST" action="{{ route('admin.orders.bulk-confirm') }}" id="bulkForm"
          x-data="{ loading: false, selectedCount: 0 }" 
          x-on:submit="if(!confirm(\`คุณต้องการยืนยันออเดอร์ \${selectedCount} รายการใช่หรือไม่?\`)) { $event.preventDefault(); } else { loading = true; }">
        @csrf
        
        <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="text-secondary-600">
                พบทั้งหมด: <span class="font-bold text-secondary-900">{{ $orders->count() }}</span> ออเดอร์
            </div>
            <x-ui.button 
                type="submit" 
                variant="success"
                id="bulkConfirmBtn"
                disabled
                x-bind:loading="loading"
                x-text="selectedCount > 0 ? \`✓ ยืนยันที่เลือก (\${selectedCount})\` : '✓ ยืนยันที่เลือก'"
            />
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-secondary-100">
                    <tr>
                        <th class="border border-secondary-300 p-3 text-left">
                            <input 
                                type="checkbox" 
                                id="selectAll" 
                                class="w-4 h-4 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                            >
                        </th>
                        <th class="border border-secondary-300 p-3 text-left">หมายเลขออเดอร์</th>
                        <th class="border border-secondary-300 p-3 text-left">ลูกค้า</th>
                        <th class="border border-secondary-300 p-3 text-left">สถานะ</th>
                        <th class="border border-secondary-300 p-3 text-right">ยอดรวม</th>
                        <th class="border border-secondary-300 p-3 text-left">สร้างเมื่อ</th>
                        <th class="border border-secondary-300 p-3 text-center">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="hover:bg-secondary-50 transition-smooth {{ $loop->even ? 'bg-secondary-50/50' : '' }}">
                            <td class="border border-secondary-200 p-3">
                                @if($order->status_id === \App\Models\ProductStatus::PENDING)
                                    <input 
                                        type="checkbox" 
                                        name="order_ids[]" 
                                        value="{{ $order->order_id }}"
                                        class="order-checkbox w-4 h-4 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                                    >
                                @endif
                            </td>
                            <td class="border border-secondary-200 p-3">
                                <span class="font-semibold text-primary-600">{{ $order->order_number }}</span>
                            </td>
                            <td class="border border-secondary-200 p-3">
                                <div class="font-semibold text-secondary-900">{{ $order->user->full_name }}</div>
                                <div class="text-sm text-secondary-600">{{ $order->user->email }}</div>
                            </td>
                            <td class="border border-secondary-200 p-3">
                                <x-ui.badge :status="$order->status_id === \App\Models\ProductStatus::PENDING ? 'pending' : 'confirmed'">
                                    {{ $order->status->status_name }}
                                </x-ui.badge>
                            </td>
                            <td class="border border-secondary-200 p-3 text-right font-semibold text-success-600">
                                ฿{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="border border-secondary-200 p-3 text-sm text-secondary-600">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="border border-secondary-200 p-3 text-center">
                                <button 
                                    type="button" 
                                    class="text-primary-600 hover:text-primary-700 font-semibold toggle-details transition-smooth"
                                    data-order-id="{{ $order->order_id }}"
                                >
                                    ▼ แสดง
                                </button>
                            </td>
                        </tr>
                        <tr class="order-details hidden" id="details-{{ $order->order_id }}">
                            <td colspan="7" class="border border-secondary-200 p-4 bg-info-50">
                                <div class="max-w-4xl mx-auto">
                                    <h4 class="font-bold mb-3 text-info-900">📋 รายละเอียดออเดอร์</h4>
                                    
                                    <table class="w-full mb-4">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="border border-secondary-300 p-2 text-left text-sm">สินค้า</th>
                                                <th class="border border-secondary-300 p-2 text-right text-sm">ราคา</th>
                                                <th class="border border-secondary-300 p-2 text-center text-sm">จำนวน</th>
                                                <th class="border border-secondary-300 p-2 text-right text-sm">รวม</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            @foreach($order->orderDetails as $detail)
                                                <tr>
                                                    <td class="border border-secondary-200 p-2">
                                                        <div class="font-semibold text-secondary-900">{{ $detail->product->product_name }}</div>
                                                        <div class="text-sm text-secondary-500">{{ $detail->product_number }}</div>
                                                    </td>
                                                    <td class="border border-secondary-200 p-2 text-right">฿{{ number_format($detail->unit_price, 2) }}</td>
                                                    <td class="border border-secondary-200 p-2 text-center">{{ $detail->quantity }}</td>
                                                    <td class="border border-secondary-200 p-2 text-right font-semibold">฿{{ number_format($detail->subtotal, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    
                                    @if($order->shipping_address)
                                        <div class="bg-white p-3 rounded border border-secondary-300">
                                            <div class="font-bold mb-2 text-secondary-900">📍 ที่อยู่จัดส่ง:</div>
                                            <p class="text-secondary-700 whitespace-pre-line leading-relaxed">{{ $order->shipping_address }}</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border border-secondary-200 p-8">
                                <x-ui.empty-state 
                                    icon="🔍" 
                                    :title="$search ? 'ไม่พบออเดอร์ที่ค้นหา' : 'ยังไม่มีออเดอร์ในระบบ'"
                                    :description="$search ? 'ลองค้นหาด้วยคำอื่น หรือลบคำค้นหา' : ''"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
</x-ui.card>

@push('scripts')
<script>
    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkButton();
    });

    // Individual checkboxes
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButton);
    });

    // Update bulk confirm button state
    function updateBulkButton() {
        const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
        const bulkBtn = document.getElementById('bulkConfirmBtn');
        
        // Update Alpine.js data
        const form = document.getElementById('bulkForm');
        if (form && form.__x) {
            form.__x.$data.selectedCount = checkedCount;
        }
        
        bulkBtn.disabled = checkedCount === 0;
    }

    // Toggle order details
    document.querySelectorAll('.toggle-details').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const detailsRow = document.getElementById(`details-${orderId}`);
            
            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
                this.textContent = '▲ ซ่อน';
            } else {
                detailsRow.classList.add('hidden');
                this.textContent = '▼ แสดง';
            }
        });
    });

</script>
@endpush
@endsection
