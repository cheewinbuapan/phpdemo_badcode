<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOrdersRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display all orders with optional search
     */
    public function index(SearchOrdersRequest $request): View
    {
        $search = $request->input('search');
        
        $query = Order::with('user', 'status', 'orderDetails.product');

        if ($search) {
            // Search by order number or user name/email
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->get();

        return view('admin.orders.index', compact('orders', 'search'));
    }

    /**
     * Bulk confirm selected orders
     */
    public function bulkConfirm(Request $request): RedirectResponse
    {
        // Validate order IDs
        $validated = $request->validate([
            'order_ids' => [
                'required',
                'array',
                'min:1'
            ],
            'order_ids.*' => [
                'required',
                'integer',
                'exists:orders,order_id'
            ]
        ], [
            'order_ids.required' => 'กรุณาเลือกออเดอร์อย่างน้อย 1 รายการ',
            'order_ids.min' => 'กรุณาเลือกออเดอร์อย่างน้อย 1 รายการ',
        ]);

        try {
            $count = $this->orderService->bulkConfirmOrders($validated['order_ids']);
            
            return back()->with('success', "ยืนยันออเดอร์สำเร็จ {$count} รายการ");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
}
