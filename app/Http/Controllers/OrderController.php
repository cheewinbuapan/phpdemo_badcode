<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display user's order history
     */
    public function index(): View
    {
        $orders = auth()->user()
            ->orders()
            ->with('status', 'orderDetails.product')
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create(): View
    {
        $products = Product::orderBy('product_name')->get();
        
        return view('orders.create', compact('products'));
    }

    /**
     * Store a newly created order
     */
    public function store(CreateOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->createOrder(
                auth()->user(),
                $request->validated()['products']
            );

            return redirect()
                ->route('orders.show', $order->order_number)
                ->with('success', 'สร้างออเดอร์สำเร็จ หมายเลขออเดอร์: ' . $order->order_number);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified order
     */
    public function show(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('orderDetails.product', 'status')
            ->firstOrFail();

        // Authorize view access
        $this->authorize('view', $order);

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the order
     */
    public function edit(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('orderDetails.product')
            ->firstOrFail();

        // Authorize update access
        $this->authorize('update', $order);

        $products = Product::orderBy('product_name')->get();

        return view('orders.edit', compact('order', 'products'));
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Authorize update access
        $this->authorize('update', $order);

        try {
            $this->orderService->updateOrder(
                $order,
                $request->validated()['products']
            );

            return redirect()
                ->route('orders.show', $order->order_number)
                ->with('success', 'อัพเดตออเดอร์สำเร็จ');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the order confirmation form
     */
    public function showConfirmForm(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('orderDetails.product', 'status')
            ->firstOrFail();

        // Authorize confirm access
        $this->authorize('confirm', $order);

        return view('orders.confirm', compact('order'));
    }

    /**
     * Confirm the order with shipping address
     */
    public function confirm(ConfirmOrderRequest $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Authorize confirm access
        $this->authorize('confirm', $order);

        try {
            $this->orderService->confirmOrder(
                $order,
                $request->validated()['shipping_address']
            );

            return redirect()
                ->route('orders.show', $order->order_number)
                ->with('success', 'ยืนยันออเดอร์สำเร็จ!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
}
