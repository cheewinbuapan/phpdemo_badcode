<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // Statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status_id', ProductStatus::PENDING)->count();
        $confirmedOrders = Order::where('status_id', ProductStatus::CONFIRMED)->count();
        $totalRevenue = Order::where('status_id', ProductStatus::CONFIRMED)->sum('total_amount');
        $totalUsers = User::count();
        $totalProducts = Product::count();

        // Calculate trends (compare with previous period)
        $currentMonthRevenue = Order::where('status_id', ProductStatus::CONFIRMED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        $previousMonthRevenue = Order::where('status_id', ProductStatus::CONFIRMED)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_amount');
        
        $revenueTrend = 'neutral';
        if ($previousMonthRevenue > 0) {
            $revenueChange = (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100;
            if ($revenueChange > 5) {
                $revenueTrend = 'up';
            } elseif ($revenueChange < -5) {
                $revenueTrend = 'down';
            }
        } elseif ($currentMonthRevenue > 0) {
            $revenueTrend = 'up';
        }

        // Current month orders vs previous month
        $currentMonthOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $previousMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $ordersTrend = 'neutral';
        if ($previousMonthOrders > 0) {
            $ordersChange = (($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100;
            if ($ordersChange > 5) {
                $ordersTrend = 'up';
            } elseif ($ordersChange < -5) {
                $ordersTrend = 'down';
            }
        } elseif ($currentMonthOrders > 0) {
            $ordersTrend = 'up';
        }

        // Orders by date (last 30 days)
        $ordersByDate = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates with zero
        $dates = [];
        $counts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('d/m');
            
            $found = $ordersByDate->firstWhere('date', $date);
            $counts[] = $found ? $found->count : 0;
        }

        // Orders by status
        $ordersByStatus = [
            'labels' => ['รอดำเนินการ', 'ยืนยันแล้ว'],
            'data' => [
                Order::where('status_id', ProductStatus::PENDING)->count(),
                Order::where('status_id', ProductStatus::CONFIRMED)->count(),
            ],
            'colors' => ['#F59E0B', '#22C55E'], // warning, success
        ];

        // Top 5 products
        $topProducts = OrderDetail::select(
                'products.product_name',
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('SUM(order_details.subtotal) as total_revenue')
            )
            ->join('products', 'order_details.product_id', '=', 'products.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->where('orders.status_id', ProductStatus::CONFIRMED)
            ->groupBy('products.product_id', 'products.product_name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        $topProductsData = [
            'labels' => $topProducts->pluck('product_name')->toArray(),
            'data' => $topProducts->pluck('total_quantity')->toArray(),
            'revenue' => $topProducts->pluck('total_revenue')->toArray(),
        ];

        // Recent orders (last 10)
        $recentOrders = Order::with(['user', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalOrders',
            'pendingOrders',
            'confirmedOrders',
            'totalRevenue',
            'totalUsers',
            'totalProducts',
            'revenueTrend',
            'ordersTrend',
            'dates',
            'counts',
            'ordersByStatus',
            'topProductsData',
            'recentOrders'
        ));
    }
}
