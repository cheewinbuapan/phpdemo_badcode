<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * OrderService Unit Tests
 * 
 * OWASP Compliance Testing:
 * - A08:2025 - Software and Data Integrity Failures
 * - A04:2025 - Insecure Design (transaction handling)
 */
class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed product statuses
        ProductStatus::create(['status_id' => 1, 'status_code' => 'PENDING', 'status_name' => 'รอดำเนินการ']);
        ProductStatus::create(['status_id' => 2, 'status_code' => 'CONFIRMED', 'status_name' => 'ยืนยันแล้ว']);

        $this->orderService = new OrderService();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 50,
        ]);
    }

    /**
     * Test createOrder method creates order with details
     */
    public function test_create_order_creates_order_and_details(): void
    {
        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                ],
            ],
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($this->user->user_id, $order->user_id);
        $this->assertEquals(ProductStatus::PENDING, $order->status_id);
        $this->assertCount(1, $order->orderDetails);
    }

    /**
     * Test createOrder generates unique order number
     */
    public function test_create_order_generates_order_number(): void
    {
        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1],
            ],
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    /**
     * Test createOrder calculates total amount correctly
     */
    public function test_create_order_calculates_total_correctly(): void
    {
        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 3,
                ],
            ],
        ];

        $order = $this->orderService->createOrder($orderData);

        $expectedTotal = $this->product->price * 3;
        $this->assertEquals($expectedTotal, $order->total_amount);
    }

    /**
     * Test updateOrder updates order items
     */
    public function test_update_order_updates_items(): void
    {
        // Create initial order
        $order = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $newProduct = Product::factory()->create(['price' => 50.00]);

        $updateData = [
            'items' => [
                [
                    'product_id' => $newProduct->product_id,
                    'quantity' => 5,
                ],
            ],
        ];

        $updatedOrder = $this->orderService->updateOrder($order, $updateData);

        $this->assertEquals(250.00, $updatedOrder->total_amount);
        $this->assertCount(1, $updatedOrder->orderDetails);
    }

    /**
     * Test confirmOrder updates status and address
     */
    public function test_confirm_order_updates_status(): void
    {
        $order = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $shippingAddress = '123 Test Street, Bangkok 10100';

        $confirmedOrder = $this->orderService->confirmOrder($order, $shippingAddress);

        $this->assertEquals(ProductStatus::CONFIRMED, $confirmedOrder->status_id);
        $this->assertEquals($shippingAddress, $confirmedOrder->shipping_address);
    }

    /**
     * Test transaction rollback on error (A08:2025 - CRITICAL)
     */
    public function test_create_order_rolls_back_on_error(): void
    {
        // Create order data with invalid product (will cause error)
        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                [
                    'product_id' => 99999, // Non-existent product
                    'quantity' => 1,
                ],
            ],
        ];

        try {
            $this->orderService->createOrder($orderData);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Exception expected
        }

        // Verify no order was created (transaction rolled back)
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->user_id,
        ]);
    }

    /**
     * Test bulkConfirmOrders confirms multiple orders atomically
     */
    public function test_bulk_confirm_orders_confirms_all(): void
    {
        $order1 = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);
        $order2 = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $this->orderService->bulkConfirmOrders([$order1->order_id, $order2->order_id]);

        $this->assertDatabaseHas('orders', [
            'order_id' => $order1->order_id,
            'status_id' => ProductStatus::CONFIRMED,
        ]);
        $this->assertDatabaseHas('orders', [
            'order_id' => $order2->order_id,
            'status_id' => ProductStatus::CONFIRMED,
        ]);
    }

    /**
     * Test createOrder uses database transaction
     */
    public function test_create_order_uses_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1],
            ],
        ];

        $this->orderService->createOrder($orderData);
    }

    /**
     * Test createOrder with multiple products
     */
    public function test_create_order_with_multiple_products(): void
    {
        $product2 = Product::factory()->create(['price' => 75.00]);

        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 2],
                ['product_id' => $product2->product_id, 'quantity' => 3],
            ],
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertCount(2, $order->orderDetails);
        $expectedTotal = ($this->product->price * 2) + ($product2->price * 3);
        $this->assertEquals($expectedTotal, $order->total_amount);
    }

    /**
     * Test updateOrder clears old items before adding new ones
     */
    public function test_update_order_clears_old_items(): void
    {
        // Create order with initial product
        $orderData = [
            'user_id' => $this->user->user_id,
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1],
            ],
        ];
        $order = $this->orderService->createOrder($orderData);

        $initialDetailCount = $order->orderDetails()->count();

        // Update with different product
        $newProduct = Product::factory()->create();
        $updateData = [
            'items' => [
                ['product_id' => $newProduct->product_id, 'quantity' => 2],
            ],
        ];

        $updatedOrder = $this->orderService->updateOrder($order, $updateData);

        // Should only have new product, not both
        $this->assertCount(1, $updatedOrder->fresh()->orderDetails);
    }
}
