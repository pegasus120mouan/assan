<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_manage_categories(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_must_choose_an_icon_when_creating_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [
                'name' => 'Sans icône',
                'status' => Status::Active->value,
            ])
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('icon');
    }

    public function test_admin_can_create_a_category_with_generated_slug(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Smartphones & Accessoires',
                'status' => Status::Active->value,
                'icon' => 'smartphone',
                'image' => UploadedFile::fake()->image('phones.jpg'),
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->first();

        $this->assertNotNull($category);
        $this->assertSame('smartphones-accessoires', $category->slug);
        $this->assertSame('smartphone', $category->icon);
        $this->assertTrue(Storage::disk('public')->exists($category->image));
        $this->assertTrue(AuditLog::query()->where('action', 'Catégorie créée')->exists());
    }

    public function test_admin_can_update_and_search_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $parent = Category::factory()->create(['name' => 'Audio']);
        $category = Category::factory()->create(['name' => 'Écouteurs', 'slug' => 'ecouteurs']);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Écouteurs Bluetooth',
                'slug' => 'ecouteurs-bluetooth',
                'parent_id' => $parent->id,
                'status' => Status::Inactive->value,
                'sort_order' => 2,
                'icon' => 'headphones',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSame('Écouteurs Bluetooth', $category->fresh()->name);
        $this->assertTrue($category->fresh()->parent->is($parent));

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['q' => 'Bluetooth', 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('Écouteurs Bluetooth', false);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => $category->name,
                'parent_id' => $category->id,
                'status' => Status::Active->value,
                'icon' => 'grid',
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_and_delete_a_brand(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.brands.store'), [
                'name' => 'OVL Labs',
                'status' => Status::Active->value,
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('admin.brands.index'));

        $brand = Brand::query()->first();
        $this->assertSame('ovl-labs', $brand->slug);

        $this->actingAs($admin)
            ->delete(route('admin.brands.destroy', $brand))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_brand_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $brand = Brand::factory()->create();
        Product::factory()->for($brand)->create();

        $this->actingAs($admin)
            ->delete(route('admin.brands.destroy', $brand))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }

    public function test_manager_can_access_category_index(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('admin.categories.index'))
            ->assertOk();
    }
}
