<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_active_categories_even_without_products(): void
    {
        $visible = Category::factory()->create(['name' => 'Téléphonies & Accessoires', 'status' => Status::Active]);
        Category::factory()->create(['name' => 'Cachée', 'status' => Status::Inactive]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Téléphonies & Accessoires')
            ->assertDontSee('Cachée')
            ->assertSee(route('catalog.category', $visible), false);
    }

    public function test_catalog_lists_only_published_products(): void
    {
        $category = Category::factory()->create(['name' => 'Audio']);
        Product::factory()->for($category)->create(['name' => 'Enceinte OVL', 'status' => Status::Active]);
        Product::factory()->for($category)->create(['name' => 'Brouillon', 'status' => Status::Inactive]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Enceinte OVL', false)
            ->assertDontSee('Brouillon', false);

        $this->get(route('catalog.category', $category))
            ->assertOk()
            ->assertSee('Enceinte OVL', false);
    }

    public function test_inactive_category_and_product_are_not_public(): void
    {
        $category = Category::factory()->create(['status' => Status::Inactive]);
        $product = Product::factory()->create(['status' => Status::Inactive]);

        $this->get(route('catalog.category', $category))->assertNotFound();
        $this->get(route('catalog.product', $product))->assertNotFound();
    }

    public function test_product_page_shows_price_gallery_and_approved_reviews(): void
    {
        $product = Product::factory()->create([
            'name' => 'Powerbank 20 000 mAh',
            'selling_price' => 12500,
            'compare_price' => 15000,
            'status' => Status::Active,
        ]);
        Review::factory()->for($product)->approved()->create([
            'title' => 'Très bon produit',
            'rating' => 5,
        ]);
        Review::factory()->for($product)->create([
            'title' => 'Avis en attente',
        ]);

        $this->get(route('catalog.product', $product))
            ->assertOk()
            ->assertSee('Powerbank 20 000 mAh', false)
            ->assertSee('12 500 FCFA', false)
            ->assertSee('UGS', false)
            ->assertSee('Ajouter Au Panier', false)
            ->assertSee('Achetez', false)
            ->assertSee('Commander sur WhatsApp', false)
            ->assertSee('String.fromCharCode(10)', false)
            ->assertDontSee('\\nProduit', false)
            ->assertSee('Très bon produit', false)
            ->assertDontSee('Avis en attente', false);
    }

    public function test_search_finds_products_by_name_sku_and_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'OVL Labs']);
        Product::factory()->for($brand)->create([
            'name' => 'Chargeur USB-C',
            'sku' => 'CHG-001',
            'status' => Status::Active,
        ]);
        Product::factory()->create(['name' => 'Autre article', 'status' => Status::Active]);

        $this->get(route('search', ['q' => 'CHG-001']))
            ->assertOk()
            ->assertSee('Chargeur USB-C', false)
            ->assertDontSee('Autre article', false);

        $this->get(route('search.suggest', ['q' => 'OVL Labs']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Chargeur USB-C']);
    }

    public function test_search_shows_a_message_when_no_product_matches(): void
    {
        Product::factory()->create(['name' => 'Chargeur USB-C', 'status' => Status::Active]);

        $this->get(route('search', ['q' => 'SS']))
            ->assertOk()
            ->assertSee('Aucun produit trouvé', false)
            ->assertSee('Aucun article ne correspond à « SS ».', false)
            ->assertDontSee('Chargeur USB-C', false);
    }

    public function test_unknown_product_shows_a_friendly_not_found_page(): void
    {
        $this->get('/produit/produit-inconnu-ss')
            ->assertNotFound()
            ->assertSee('Produit ou page introuvable', false);
    }

    public function test_catalog_filters_by_price_and_availability(): void
    {
        Product::factory()->inStock(5)->create([
            'name' => 'Câble court',
            'selling_price' => 2000,
            'status' => Status::Active,
        ]);
        Product::factory()->outOfStock()->create([
            'name' => 'Câble long',
            'selling_price' => 9000,
            'status' => Status::Active,
        ]);

        $this->get(route('catalog.index', ['max_price' => 3000, 'in_stock' => 1]))
            ->assertOk()
            ->assertSee('Câble court', false)
            ->assertDontSee('Câble long', false);
    }
}
