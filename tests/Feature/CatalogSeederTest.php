<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_admin_seeders_create_expected_records(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->assertGreaterThanOrEqual(9, Category::query()->count());
        $this->assertGreaterThanOrEqual(10, Brand::query()->count());
        $this->assertGreaterThanOrEqual(30, Product::query()->count());
        $this->assertTrue(User::query()->where('email', config('shop.admin.email'))->exists());
        $this->assertTrue(Product::query()->where('stock_quantity', 0)->exists());
        $this->assertTrue(Product::query()->where('stock_quantity', '>', 0)->exists());
    }
}
