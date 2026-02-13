<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Order Management Tests
 * 
 * OWASP Compliance Testing:
 * - A01:2025 - Broken Access Control (CRITICAL)
 * - A03:2025 - Injection (SQL injection prevention)
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed product statuses
        ProductStatus::create(['status_id' => 1, 'status_code' => 'PENDING', 'status_name' => 'รอดำเนินการ']);
        ProductStatus::create(['status_id' => 2, 'status_code' => 'CONFIRMED', 'status_name' => 'ยืนยันแล้ว']);

        // Create test users
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // Create test products
        $this->product = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 50,
        ]);
    }

    /**
     * Test user can create order with valid products
     */
    public function test_user_can_create_order_with_products(): void
    {
        $response = $this->actingAs($this->user)->post('/orders', [
            'products' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->user_id,
            'status_id' => ProductStatus::PENDING,
        ]);
    }

    /**
     * Test user can view their own orders
     */
    public function test_user_can_view_own_orders(): void
    {
        $order = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->get("/orders/{$order->order_number}");

        $response->assertStatus(200);
        $response->assertViewIs('orders.show');
        $response->assertViewHas('order', $order);
    }

    /**
     * Test user CANNOT view other user's orders (A01:2025 - CRITICAL)
     */
    public function test_user_cannot_view_other_users_orders(): void
    {
        $otherOrder = Order::factory()->for($this->otherUser, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->get("/orders/{$otherOrder->order_number}");

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test user can update their own PENDING order
     */
    public function test_user_can_update_own_pending_order(): void
    {
        $order = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $newProduct = Product::factory()->create();

        $response = $this->actingAs($this->user)->put("/orders/{$order->order_number}", [
            'products' => [
                [
                    'product_id' => $newProduct->product_id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertRedirect();
    }

    /**
     * Test user CANNOT update other user's order (A01:2025 - CRITICAL)
     */
    public function test_user_cannot_update_other_users_order(): void
    {
        $otherOrder = Order::factory()->for($this->otherUser, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->put("/orders/{$otherOrder->order_number}", [
            'products' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test user CANNOT update CONFIRMED order
     */
    public function test_user_cannot_update_confirmed_order(): void
    {
        $order = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::CONFIRMED,
        ]);

        $response = $this->actingAs($this->user)->put("/orders/{$order->order_number}", [
            'products' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(403); // Cannot modify confirmed order
    }

    /**
     * Test user can confirm their own PENDING order
     */
    public function test_user_can_confirm_own_pending_order(): void
    {
        $order = Order::factory()->for($this->user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->post("/orders/{$order->order_number}/confirm", [
            'shipping_address' => '123 Test Street, Bangkok 10100',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'order_id' => $order->order_id,
            'status_id' => ProductStatus::CONFIRMED,
            'shipping_address' => '123 Test Street, Bangkok 10100',
        ]);
    }

    /**
     * Test user CANNOT confirm other user's order (A01:2025 - CRITICAL)
     */
    public function test_user_cannot_confirm_other_users_order(): void
    {
        $otherOrder = Order::factory()->for($this->otherUser, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->post("/orders/{$otherOrder->order_number}/confirm", [
            'shipping_address' => '123 Test Street',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test order requires at least one product
     */
    public function test_order_requires_at_least_one_product(): void
    {
        $response = $this->actingAs($this->user)->post('/orders', [
            'products' => [],
        ]);

        $response->assertSessionHasErrors('products');
    }

    /**
     * Test order validation rejects invalid product IDs (A03 protection)
     */
    public function test_order_rejects_invalid_product_ids(): void
    {
        $response = $this->actingAs($this->user)->post('/orders', [
            'products' => [
                [
                    'product_id' => 99999, // Non-existent product
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('products.0.product_id');
    }

    /**
     * Test guest cannot create orders
     */
    public function test_guest_cannot_create_order(): void
    {
        $response = $this->post('/orders', [
            'products' => [
                ['product_id' => $this->product->product_id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test admin can view all users' orders
     */
    public function test_admin_can_view_any_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->for($this->user, 'user')->create();

        $response = $this->actingAs($admin)->get("/orders/{$order->order_number}");

        $response->assertStatus(200);
    }
}
