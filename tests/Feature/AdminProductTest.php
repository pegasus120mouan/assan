<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_manage_products(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_product_with_generated_slug_and_sku(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Powerbank 20 000 mAh',
                'selling_price' => 12500,
                'status' => Status::Active->value,
                'stock_quantity' => 15,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->first();

        $this->assertNotNull($product);
        $this->assertSame('powerbank-20-000-mah', $product->slug);
        $this->assertNotSame('', $product->sku);
        $this->assertSame(12500, $product->selling_price);
        $this->assertTrue(AuditLog::query()->where('action', 'Produit créé')->exists());
    }

    public function test_admin_can_upload_images_and_variants(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => 'Écouteurs Bluetooth',
                'selling_price' => 8900,
                'status' => Status::Active->value,
                'images' => [
                    UploadedFile::fake()->image('front.jpg'),
                    UploadedFile::fake()->image('side.png'),
                ],
                'variants' => [
                    [
                        'name' => 'Noir',
                        'sku' => 'EAR-NOIR',
                        'price' => 8900,
                        'stock_quantity' => 8,
                        'status' => Status::Active->value,
                        'options' => '{"couleur":"Noir"}',
                    ],
                    [
                        'name' => 'Blanc',
                        'sku' => 'EAR-BLANC',
                        'stock_quantity' => 4,
                        'status' => Status::Active->value,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->with(['images', 'variants'])->first();

        $this->assertCount(2, $product->images);
        $this->assertSame(1, $product->images->where('is_primary', true)->count());
        $this->assertTrue(Storage::disk('public')->exists($product->images->first()->image));
        $this->assertCount(2, $product->variants);
        $this->assertSame(['couleur' => 'Noir'], $product->variants->firstWhere('name', 'Noir')->options);
        $this->assertSame(12, $product->availableStock());
    }

    public function test_admin_can_search_and_filter_products(): void
    {
        $admin = User::factory()->admin()->create();
        $audio = Category::factory()->create(['name' => 'Audio']);
        Product::factory()->for($audio)->create([
            'name' => 'Enceinte portable',
            'sku' => 'SPK-100',
            'status' => Status::Inactive,
            'featured' => true,
            'stock_quantity' => 3,
        ]);
        Product::factory()->create(['name' => 'Câble USB', 'sku' => 'CBL-100', 'featured' => false]);

        $this->actingAs($admin)
            ->get(route('admin.products.index', [
                'q' => 'Enceinte',
                'status' => 'inactive',
                'category_id' => $audio->id,
                'featured' => 1,
            ]))
            ->assertOk()
            ->assertSee('Enceinte portable', false)
            ->assertDontSee('Câble USB', false);
    }

    public function test_admin_can_soft_delete_and_restore_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Support téléphone']);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted($product);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['trashed' => 'only']))
            ->assertOk()
            ->assertSee('Support téléphone', false);

        $this->actingAs($admin)
            ->post(route('admin.products.restore', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertNotSoftDeleted($product);
        $this->assertTrue(AuditLog::query()->where('action', 'Produit restauré')->exists());
    }

    public function test_product_with_order_items_cannot_be_force_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        OrderItem::factory()->for($product)->create();
        $product->delete();

        $this->actingAs($admin)
            ->delete(route('admin.products.force-destroy', $product))
            ->assertSessionHasErrors('delete');

        $this->assertSoftDeleted($product);
    }

    public function test_admin_can_force_delete_a_trashed_product_without_orders(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $product->images()->create([
            'image' => 'products/1/cover.jpg',
            'alt' => $product->name,
            'sort_order' => 1,
            'is_primary' => true,
        ]);
        Storage::disk('public')->put('products/1/cover.jpg', 'fake');
        $product->delete();

        $this->actingAs($admin)
            ->delete(route('admin.products.force-destroy', $product))
            ->assertRedirect(route('admin.products.index', ['trashed' => 'only']));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertFalse(Storage::disk('public')->exists('products/1/cover.jpg'));
    }

    public function test_admin_can_export_and_import_products_via_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['slug' => 'smartphones']);
        Product::factory()->for($category)->create([
            'name' => 'Coque iPhone',
            'sku' => 'CASE-001',
            'selling_price' => 3500,
        ]);

        $export = $this->actingAs($admin)->get(route('admin.products.export'));
        $export->assertOk();
        $this->assertStringContainsString('CASE-001', $export->streamedContent());

        $csv = "name;sku;category_slug;selling_price;stock_quantity;status\nChargeur USB-C;CHG-001;smartphones;4500;12;active\n";
        $file = UploadedFile::fake()->createWithContent('produits.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.products.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'CHG-001',
            'name' => 'Chargeur USB-C',
            'selling_price' => 4500,
            'stock_quantity' => 12,
        ]);
    }

    public function test_updating_a_product_can_remove_a_variant_and_set_primary_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $keep = ProductVariant::factory()->for($product)->create(['name' => '64 Go']);
        ProductVariant::factory()->for($product)->create(['name' => '128 Go']);
        $first = $product->images()->create([
            'image' => 'products/'.$product->id.'/a.jpg',
            'sort_order' => 1,
            'is_primary' => true,
        ]);
        $second = $product->images()->create([
            'image' => 'products/'.$product->id.'/b.jpg',
            'sort_order' => 2,
            'is_primary' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'category_id' => $product->category_id,
                'name' => $product->name,
                'selling_price' => $product->selling_price,
                'status' => Status::Active->value,
                'primary_image_id' => $second->id,
                'variants' => [
                    [
                        'id' => $keep->id,
                        'name' => '64 Go',
                        'sku' => $keep->sku,
                        'stock_quantity' => 7,
                        'status' => Status::Active->value,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('product_variants', ['name' => '128 Go']);
        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(7, $keep->fresh()->stock_quantity);
    }

    public function test_long_title_pasted_in_sku_still_creates_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $title = 'Adaptateur audio 2en1 Mcdodo – Sortie Lightning pour iPhone – Entrée 1 port Lightning pour charge 60W et 1 port Jack';

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'sku' => $title,
                'selling_price' => 6000,
                'status' => Status::Active->value,
                'stock_quantity' => 0,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->first();

        $this->assertNotNull($product);
        $this->assertSame($title, $product->name);
        $this->assertNotSame($title, $product->sku);
        $this->assertTrue(mb_strlen($product->sku) <= 64);
    }

    public function test_product_resume_can_exceed_500_characters(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $resume = str_repeat('Coach sommeil. ', 50);

        $this->assertTrue(mb_strlen($resume) > 500);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'WHOOP 5.0',
                'selling_price' => 150000,
                'status' => Status::Active->value,
                'short_description' => $resume,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertGreaterThan(500, mb_strlen((string) Product::query()->first()?->short_description));
    }

    public function test_manager_can_open_product_index(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Produits', false)
            ->assertSee('admin-confirm-delete', false);
    }
}
