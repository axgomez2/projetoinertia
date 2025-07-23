<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\CatStyleShop;
use App\Models\MidiaStatus;
use App\Models\CoverStatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with role 66
        $this->adminUser = User::factory()->create(['role' => 66]);
    }

    /** @test */
    public function admin_can_access_settings_index()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_category()
    {
        $categoryData = [
            'name' => 'Rock Clássico',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.categories.store'), $categoryData);

        $response->assertRedirect();
        $this->assertDatabaseHas('cat_style_shop', [
            'name' => 'Rock Clássico',
            'slug' => 'rock-classico',
        ]);
    }

    /** @test */
    public function admin_can_create_media_status()
    {
        $mediaStatusData = [
            'title' => 'Mint (M)',
            'description' => 'Perfeito estado, sem uso',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.media-status.store'), $mediaStatusData);

        $response->assertRedirect();
        $this->assertDatabaseHas('midia_status', $mediaStatusData);
    }

    /** @test */
    public function admin_can_create_cover_status()
    {
        $coverStatusData = [
            'title' => 'Near Mint (NM)',
            'description' => 'Quase perfeito, mínimos sinais de uso',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.cover-status.store'), $coverStatusData);

        $response->assertRedirect();
        $this->assertDatabaseHas('cover_status', $coverStatusData);
    }

    /** @test */
    public function admin_can_create_supplier()
    {
        $supplierData = [
            'name' => 'Distribuidora Musical LTDA',
            'email' => 'contato@distribuidora.com',
            'phone' => '(11) 99999-9999',
            'document' => '12.345.678/0001-90',
            'document_type' => 'cnpj',
            'city' => 'São Paulo',
            'state' => 'SP',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.suppliers.store'), $supplierData);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', $supplierData);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = CatStyleShop::factory()->create(['name' => 'Rock']);

        $updateData = ['name' => 'Rock Progressivo'];

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.settings.categories.update', $category), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('cat_style_shop', [
            'id' => $category->id,
            'name' => 'Rock Progressivo',
            'slug' => 'rock-progressivo',
        ]);
    }

    /** @test */
    public function admin_can_delete_unused_category()
    {
        $category = CatStyleShop::factory()->create();

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.settings.categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cat_style_shop', ['id' => $category->id]);
    }

    /** @test */
    public function non_admin_cannot_access_settings()
    {
        $regularUser = User::factory()->create(['role' => 20]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.settings.index'));

        // The role middleware redirects instead of returning 403
        $response->assertStatus(302);
    }

    /** @test */
    public function guest_cannot_access_settings()
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('login'));
    }
}
