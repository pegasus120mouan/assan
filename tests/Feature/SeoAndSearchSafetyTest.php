<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndSearchSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_and_robots_are_public(): void
    {
        $category = Category::factory()->create(['name' => 'Audio']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Enceinte test',
            'status' => 'active',
        ]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /compte', false)
            ->assertSee('Disallow: /panier', false)
            ->assertSee('Disallow: /commande', false)
            ->assertSee('Sitemap:', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('catalog.product', $product), false)
            ->assertSee(route('catalog.category', $category), false);
    }

    public function test_search_rejects_junk_input_without_server_error(): void
    {
        Product::factory()->create(['name' => 'Chargeur USB-C']);

        $this->get(route('search', ['q' => ['not' => 'a-string']]))
            ->assertOk();

        $this->get(route('search', ['q' => str_repeat('a', 200)]))
            ->assertOk();

        $this->get(route('search', ['q' => '%_\'"><']))
            ->assertOk();

        $this->get(route('search.suggest', ['q' => '%_']))
            ->assertOk()
            ->assertJsonStructure(['products', 'categories']);
    }
}
