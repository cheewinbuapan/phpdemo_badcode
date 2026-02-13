<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin Order Management Tests
 * 
 * OWASP Compliance Testing:
 * - A01:2025 - Broken Access Control (admin authorization)
 * - A03:2025 - Injection (search input validation)
 */
class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed product statuses
        ProductStatus::create(['status_id' => 1, 'status_code' => 'PENDING', 'status_name' => 'รอดำเนินการ']);
        ProductStatus::create(['status_id' => 2, 'status_code' => 'CONFIRMED', 'status_name' => 'ยืนยันแล้ว']);

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
    }

    /**
     * Test admin can access admin orders page
     */
    public function test_admin_can_access_admin_orders_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
    }

    /**
     * Test regular user CANNOT access admin orders page (A01:2025 - CRITICAL)
     */
    public function test_regular_user_cannot_access_admin_orders(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/orders');

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test guest CANNOT access admin orders page
     */
    public function test_guest_cannot_access_admin_orders(): void
    {
        $response = $this->get('/admin/orders');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin can search orders by order number
     */
    public function test_admin_can_search_orders_by_order_number(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user, 'user')->create([
            'order_number' => 'ORD-123456',
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/orders?search=ORD-123456');

        $response->assertStatus(200);
        $response->assertSee('ORD-123456');
    }

    /**
     * Test admin can search orders by user email
     */
    public function test_admin_can_search_orders_by_user_email(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $order = Order::factory()->for($user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/orders?search=customer@example.com');

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    /**
     * Test search input is validated (A03:2025 - Injection prevention)
     */
    public function test_search_input_is_validated(): void
    {
        // Malicious SQL injection attempt
        $response = $this->actingAs($this->admin)->get('/admin/orders?search=\' OR 1=1--');

        // Should not cause SQL error - input is validated and parameterized
        $response->assertStatus(200);
    }

    /**
     * Test admin can bulk confirm pending orders
     */
    public function test_admin_can_bulk_confirm_orders(): void
    {
        $user = User::factory()->create();
        $order1 = Order::factory()->for($user, 'user')->create(['status_id' => ProductStatus::PENDING]);
        $order2 = Order::factory()->for($user, 'user')->create(['status_id' => ProductStatus::PENDING]);

        $response = $this->actingAs($this->admin)->post('/admin/orders/bulk-confirm', [
            'order_ids' => [$order1->order_id, $order2->order_id],
        ]);

        $response->assertRedirect();
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
     * Test regular user CANNOT bulk confirm orders (A01:2025 - CRITICAL)
     */
    public function test_regular_user_cannot_bulk_confirm_orders(): void
    {
        $order = Order::factory()->for($this->regularUser, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        $response = $this->actingAs($this->regularUser)->post('/admin/orders/bulk-confirm', [
            'order_ids' => [$order->order_id],
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test bulk confirm requires at least one order ID
     */
    public function test_bulk_confirm_requires_order_ids(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/orders/bulk-confirm', [
            'order_ids' => [],
        ]);

        $response->assertSessionHasErrors('order_ids');
    }

    /**
     * Test bulk confirm validates order IDs exist
     */
    public function test_bulk_confirm_validates_order_ids_exist(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/orders/bulk-confirm', [
            'order_ids' => [99999], // Non-existent order
        ]);

        $response->assertSessionHasErrors('order_ids.0');
    }

    /**
     * Test admin can view all orders (not just their own)
     */
    public function test_admin_can_view_all_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order1 = Order::factory()->for($user1, 'user')->create();
        $order2 = Order::factory()->for($user2, 'user')->create();

        $response = $this->actingAs($this->admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee($order1->order_number);
        $response->assertSee($order2->order_number);
    }

    /**
     * Test admin can filter by order status
     */
    public function test_admin_can_filter_by_status(): void
    {
        $user = User::factory()->create();
        $pendingOrder = Order::factory()->for($user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);
        $confirmedOrder = Order::factory()->for($user, 'user')->create([
            'status_id' => ProductStatus::CONFIRMED,
        ]);

        // Filter for pending orders only
        $response = $this->actingAs($this->admin)->get('/admin/orders?status=' . ProductStatus::PENDING);

        $response->assertStatus(200);
        $response->assertSee($pendingOrder->order_number);
    }

    /**
     * Test bulk operations are logged for audit trail (A09:2025)
     */
    public function test_bulk_confirm_is_logged(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user, 'user')->create([
            'status_id' => ProductStatus::PENDING,
        ]);

        \Illuminate\Support\Facades\Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();

        \Illuminate\Support\Facades\Log::shouldReceive('info')
            ->once();

        $this->actingAs($this->admin)->post('/admin/orders/bulk-confirm', [
            'order_ids' => [$order->order_id],
        ]);
    }
}
