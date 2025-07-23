<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\VinylSec;
use App\Models\VinylMaster;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private VinylSec $vinylSec;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user (role 66)
        $this->adminUser = User::factory()->create(['role' => 66]);

        // Create a vinyl record for testing
        $vinylMaster = VinylMaster::factory()->create([
            'title' => 'Test Album',
        ]);

        $this->vinylSec = VinylSec::factory()->create([
            'vinyl_master_id' => $vinylMaster->id,
            'price' => 50.00,
            'stock' => 10,
            'in_stock' => true,
        ]);
    }

    /** @test */
    public function admin_can_access_pos_history()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pos.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/POS/History')
            ->has('sales')
            ->has('stats')
            ->has('paymentMethods')
        );
    }

    /** @test */
    public function admin_can_access_pos_create_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pos.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/POS/DirectSale')
            ->has('paymentMethods')
        );
    }

    /** @test */
    public function admin_can_search_products()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pos.search-products', ['search' => 'Test']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'artist',
                'catalog_number',
                'internal_code',
                'price',
                'stock',
                'format',
                'midia_status',
                'cover_status',
            ]
        ]);
    }

    /** @test */
    public function admin_can_create_pos_sale()
    {
        $saleData = [
            'customer_name' => 'Test Customer',
            'payment_method' => 'money',
            'notes' => 'Test sale',
            'items' => [
                [
                    'vinyl_sec_id' => $this->vinylSec->id,
                    'quantity' => 2,
                    'price' => 50.00,
                    'item_discount' => 0,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.pos.store'), $saleData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify sale was created
        $this->assertDatabaseHas('pos_sales', [
            'customer_name' => 'Test Customer',
            'payment_method' => 'money',
            'total' => 100.00,
        ]);

        // Verify sale item was created
        $this->assertDatabaseHas('pos_sale_items', [
            'vinyl_sec_id' => $this->vinylSec->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        // Verify stock was updated
        $this->vinylSec->refresh();
        $this->assertEquals(8, $this->vinylSec->stock);
    }

    /** @test */
    public function pos_sale_creation_fails_with_insufficient_stock()
    {
        $saleData = [
            'customer_name' => 'Test Customer',
            'payment_method' => 'money',
            'items' => [
                [
                    'vinyl_sec_id' => $this->vinylSec->id,
                    'quantity' => 15, // More than available stock (10)
                    'price' => 50.00,
                    'item_discount' => 0,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.pos.store'), $saleData);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        // Verify no sale was created
        $this->assertDatabaseCount('pos_sales', 0);

        // Verify stock wasn't changed
        $this->vinylSec->refresh();
        $this->assertEquals(10, $this->vinylSec->stock);
    }

    /** @test */
    public function admin_can_view_pos_sale_details()
    {
        $posSale = PosSale::factory()->create([
            'customer_name' => 'Test Customer',
            'total' => 100.00,
        ]);

        PosSaleItem::factory()->create([
            'pos_sale_id' => $posSale->id,
            'vinyl_sec_id' => $this->vinylSec->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pos.show', $posSale));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/POS/Show')
            ->has('sale')
        );
    }

    /** @test */
    public function admin_can_get_receipt_data()
    {
        $posSale = PosSale::factory()->create([
            'customer_name' => 'Test Customer',
            'total' => 100.00,
        ]);

        PosSaleItem::factory()->create([
            'pos_sale_id' => $posSale->id,
            'vinyl_sec_id' => $this->vinylSec->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pos.receipt', $posSale));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'sale',
            'receipt_data' => [
                'store_name',
                'store_address',
                'store_phone',
                'generated_at',
            ]
        ]);
    }

    /** @test */
    public function pos_sale_validation_requires_payment_method()
    {
        $saleData = [
            'customer_name' => 'Test Customer',
            // Missing payment_method
            'items' => [
                [
                    'vinyl_sec_id' => $this->vinylSec->id,
                    'quantity' => 1,
                    'price' => 50.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.pos.store'), $saleData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    /** @test */
    public function pos_sale_validation_requires_items()
    {
        $saleData = [
            'customer_name' => 'Test Customer',
            'payment_method' => 'money',
            'items' => [] // Empty items array
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.pos.store'), $saleData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function non_admin_cannot_access_pos_system()
    {
        $regularUser = User::factory()->create(['role' => 20]); // Customer role

        $response = $this->actingAs($regularUser)
            ->get(route('admin.pos.index'));

        // The role middleware redirects unauthorized users instead of returning 403
        $response->assertStatus(302);
    }

    /** @test */
    public function guest_cannot_access_pos_system()
    {
        $response = $this->get(route('admin.pos.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function pos_sale_updates_stock_correctly()
    {
        $initialStock = $this->vinylSec->stock;

        $saleData = [
            'customer_name' => 'Test Customer',
            'payment_method' => 'credit_card',
            'items' => [
                [
                    'vinyl_sec_id' => $this->vinylSec->id,
                    'quantity' => 3,
                    'price' => 50.00,
                    'item_discount' => 0,
                ]
            ]
        ];

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.pos.store'), $saleData);

        $this->vinylSec->refresh();
        $this->assertEquals($initialStock - 3, $this->vinylSec->stock);
    }

    /** @test */
    public function pos_sale_marks_product_out_of_stock_when_stock_reaches_zero()
    {
        // Set stock to 1
        $this->vinylSec->update(['stock' => 1]);

        $saleData = [
            'customer_name' => 'Test Customer',
            'payment_method' => 'pix',
            'items' => [
                [
                    'vinyl_sec_id' => $this->vinylSec->id,
                    'quantity' => 1,
                    'price' => 50.00,
                    'item_discount' => 0,
                ]
            ]
        ];

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.pos.store'), $saleData);

        $this->vinylSec->refresh();
        $this->assertEquals(0, $this->vinylSec->stock);
        $this->assertFalse($this->vinylSec->in_stock);
    }
}
