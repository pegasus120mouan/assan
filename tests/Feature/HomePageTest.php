<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\StorefrontCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_returns_a_successful_response(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(config('shop.name'), false)
            ->assertSee('Votre site d&#039;achat en ligne.', false)
            ->assertSee('images/logo/logo.png', false)
            ->assertSee('Découvrir', false)
            ->assertSee('Deals du jour', false);
    }

    public function test_home_page_recovers_when_nav_cache_is_corrupt(): void
    {
        Category::factory()->create(['name' => 'Audio boutique']);

        Cache::put('storefront.nav_categories', new \stdClass);
        Cache::put(StorefrontCache::NAV_CATEGORIES, new \stdClass);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Audio boutique', false);
    }

    public function test_home_promo_tile_displays_the_category_image(): void
    {
        Category::factory()->create([
            'name' => 'Téléphonies & Accessoires',
            'image' => 'categories/phones.jpg',
            'sort_order' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Téléphonies', false)
            ->assertSee('/storage/categories/phones.jpg', false)
            ->assertSee('has-image', false);
    }

    public function test_home_category_card_falls_back_to_a_product_image(): void
    {
        $category = Category::factory()->create(['name' => 'Gadgets utiles']);
        $product = Product::factory()->for($category)->create();
        ProductImage::factory()->for($product)->create([
            'image' => 'products/gadget.jpg',
            'is_primary' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/products/gadget.jpg', false);
    }
}
