@extends('layouts.app')

@section('title', '- แดชบอร์ดผู้ดูแลระบบ')

@section('content')
<div class="fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-secondary-900">📊 แดชบอร์ดผู้ดูแลระบบ</h1>
            <p class="text-secondary-600 mt-1">ภาพรวมระบบจัดการออเดอร์</p>
        </div>
        <x-ui.badge variant="warning" class="text-base px-4 py-2">
            👑 ผู้ดูแลระบบ
        </x-ui.badge>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <x-admin.stat-card 
            title="ออเดอร์ทั้งหมด"
            :value="number_format($totalOrders)"
            icon="📦"
            variant="primary"
            :trend="$ordersTrend"
        />
        
        <x-admin.stat-card 
            title="รอดำเนินการ"
            :value="number_format($pendingOrders)"
            icon="⏳"
            variant="warning"
            trend="neutral"
        />
        
        <x-admin.stat-card 
            title="ยืนยันแล้ว"
            :value="number_format($confirmedOrders)"
            icon="✅"
            variant="success"
            trend="neutral"
        />
        
        <x-admin.stat-card 
            title="รายได้รวม"
            :value="'฿' . number_format($totalRevenue, 2)"
            icon="💰"
            variant="success"
            :trend="$revenueTrend"
        />
        
        <x-admin.stat-card 
            title="ผู้ใช้งาน"
            :value="number_format($totalUsers)"
            icon="👥"
            variant="info"
            trend="neutral"
        />
        
        <x-admin.stat-card 
            title="สินค้า"
            :value="number_format($totalProducts)"
            icon="🏷️"
            variant="secondary"
            trend="neutral"
        />
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Orders by Date Chart -->
        <x-admin.chart-card title="ออเดอร์ย้อนหลัง 30 วัน">
            <canvas id="ordersChart"></canvas>
        </x-admin.chart-card>

        <!-- Orders by Status Chart -->
        <x-admin.chart-card title="สัดส่วนสถานะออเดอร์">
            <canvas id="statusChart"></canvas>
        </x-admin.chart-card>
    </div>

    <!-- Top Products Chart -->
    <div class="mb-8">
        <x-admin.chart-card title="สินค้าขายดีอันดับ 1-5">
            <canvas id="productsChart"></canvas>
        </x-admin.chart-card>
    </div>

    <!-- Recent Orders Table -->
    <x-ui.card>
        <h3 class="text-xl font-bold text-secondary-900 mb-4">📋 ออเดอร์ล่าสุด</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-secondary-100">
                    <tr>
                        <th class="border border-secondary-300 p-3 text-left">หมายเลขออเดอร์</th>
                        <th class="border border-secondary-300 p-3 text-left">ลูกค้า</th>
                        <th class="border border-secondary-300 p-3 text-left">สถานะ</th>
                        <th class="border border-secondary-300 p-3 text-right">ยอดรวม</th>
                        <th class="border border-secondary-300 p-3 text-left">วันที่</th>
                        <th class="border border-secondary-300 p-3 text-center">ดูรายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-secondary-50 transition-smooth {{ $loop->even ? 'bg-secondary-50/50' : '' }}">
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
                                <x-ui.button 
                                    href="{{ route('orders.show', $order) }}" 
                                    variant="outline" 
                                    size="sm"
                                >
                                    ดูรายละเอียด
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-secondary-200 p-8">
                                <x-ui.empty-state 
                                    icon="📦" 
                                    title="ยังไม่มีออเดอร์ในระบบ"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js Configuration
    Chart.defaults.font.family = "'Inter', 'Noto Sans Thai', sans-serif";
    Chart.defaults.color = '#6B7280'; // secondary-500

    // Orders by Date Chart (Line)
    const ordersCtx = document.getElementById('ordersChart');
    new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'จำนวนออเดอร์',
                data: @json($counts),
                borderColor: '#3B82F6', // primary-500
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)', // secondary-900
                    padding: 12,
                    cornerRadius: 8,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    callbacks: {
                        label: function(context) {
                            return 'ออเดอร์: ' + context.parsed.y + ' รายการ';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#6B7280'
                    },
                    grid: {
                        color: 'rgba(107, 114, 128, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#6B7280'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Orders by Status Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($ordersByStatus['labels']),
            datasets: [{
                data: @json($ordersByStatus['data']),
                backgroundColor: @json($ordersByStatus['colors']),
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 13,
                            weight: '600'
                        },
                        color: '#374151' // secondary-700
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' รายการ (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Top Products Chart (Bar)
    const productsCtx = document.getElementById('productsChart');
    new Chart(productsCtx, {
        type: 'bar',
        data: {
            labels: @json($topProductsData['labels']),
            datasets: [{
                label: 'จำนวนที่ขายได้',
                data: @json($topProductsData['data']),
                backgroundColor: '#22C55E', // success-500
                borderRadius: 8,
                barThickness: 50,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const revenue = @json($topProductsData['revenue'])[context.dataIndex];
                            return [
                                'จำนวน: ' + context.parsed.x + ' ชิ้น',
                                'รายได้: ฿' + parseFloat(revenue).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#6B7280'
                    },
                    grid: {
                        color: 'rgba(107, 114, 128, 0.1)'
                    }
                },
                y: {
                    ticks: {
                        color: '#374151',
                        font: {
                            weight: '600'
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
