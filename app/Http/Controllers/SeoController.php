<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            url('/'),
            route('catalog.index'),
        ]);

        $urls = $urls
            ->concat(Category::query()->active()->get()->map(fn (Category $category) => route('catalog.category', $category)))
            ->concat(Product::query()->published()->get()->map(fn (Product $product) => route('catalog.product', $product)));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $loc) {
            $xml .= '  <url><loc>'.e($loc).'</loc></url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /compte',
            'Disallow: /panier',
            'Disallow: /commande',
            'Allow: /',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
