<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user (role 66)
        $this->admin = User::factory()->create([
            'role' => 66,
            'email_verified_at' => now(),
        ]);

        // Create a customer user (role 20)
        $this->customer = User::factory()->create([
            'role' => 20,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_view_orders_index()
    {
        // Create some test orders
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.online.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Orders/Online/Index')
                ->has('orders.data')
                ->has('statusOptions')
                ->has('customers')
        );
    }

    public function test_admin_can_view_order_details()
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.online.show', $order));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Orders/Online/Show')
                ->has('order')
                ->where('order.id', $order->id)
        );
    }

    public function test_admin_can_filter_orders_by_status()
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'shipped',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.online.index', ['status' => 'pending']));

        $response->assertStatus(200);
    }

    public function test_admin_can_search_orders()
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-123456',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.online.index', ['search' => 'ORD-123456']));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_orders()
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.online.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_non_admin_cannot_access_orders()
    {
        $regularUser = User::factory()->create([
            'role' => 20, // Customer role
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.orders.online.index'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Você não tem permissão para acessar esta página.');
    }

    public function test_guest_cannot_access_orders()
    {
        $response = $this->get(route('admin.orders.online.index'));

        $response->assertRedirect(route('login'));
    }
}
