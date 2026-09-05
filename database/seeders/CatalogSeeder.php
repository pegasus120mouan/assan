<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['Gadgets utiles', 'gadgets'],
            ['Accessoires smartphone', 'smartphone'],
            ['Smart Home', 'home'],
            ['Accessoires PC', 'computer'],
            ['Création de contenu', 'camera'],
            ['Accessoires automobiles', 'car'],
            ['Audio & écoute', 'speaker'],
            ['Énergie & powerbanks', 'battery'],
            ['Câbles & connectique', 'cable'],
        ])->map(function (array $row, int $index): Category {
            [$name, $icon] = $row;

            return Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => 'Rayon '.$name.' pour le quotidien à Abidjan.',
                    'status' => Status::Active,
                    'sort_order' => $index + 1,
                    'icon' => $icon,
                ]
            );
        })->keyBy('slug');

        Category::query()->where('name', 'Téléphonies & Accessoires')->update(['icon' => 'phone']);
        Category::query()->whereNull('icon')->update(['icon' => 'grid']);

        $brands = collect([
            'OVL Tech', 'Anker', 'Baseus', 'Oraimo', 'Ugreen',
            'Xiaomi', 'Samsung', 'JBL', 'TP-Link', 'Belkin',
        ])->map(function (string $name, int $index): Brand {
            return Brand::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $name,
                    'status' => Status::Active,
                ]
            );
        })->keyBy('slug');

        $bySlug = fn (string $name) => $categories->get(Str::slug($name));
        $brand = fn (string $name) => $brands->get(Str::slug($name));

        $products = [
            ['Powerbank 20 000 mAh PD 22.5W', 'Énergie & powerbanks', 'Anker', 18500, 12, true, true],
            ['Powerbank 10 000 mAh compacte', 'Énergie & powerbanks', 'Oraimo', 9500, 25, false, true],
            ['Chargeur mural 65W GaN', 'Énergie & powerbanks', 'Ugreen', 22500, 8, true, false],
            ['Chargeur voiture USB-C 30W', 'Accessoires automobiles', 'Baseus', 7500, 18, false, false],
            ['Câble USB-C 100W 2m', 'Câbles & connectique', 'Ugreen', 4500, 40, false, true],
            ['Câble Lightning 1m', 'Câbles & connectique', 'Belkin', 6500, 15, false, false],
            ['Hub USB-C 7-en-1', 'Accessoires PC', 'Baseus', 19500, 10, true, false],
            ['Support laptop aluminium', 'Accessoires PC', 'OVL Tech', 12500, 9, false, false],
            ['Souris sans fil silencieuse', 'Accessoires PC', 'Xiaomi', 8500, 22, false, true],
            ['Clavier compact Bluetooth', 'Accessoires PC', 'Xiaomi', 15900, 8, false, false],
            ['Écouteurs TWS ANC', 'Audio & écoute', 'Oraimo', 14500, 14, true, true],
            ['Enceinte Bluetooth portable', 'Audio & écoute', 'JBL', 29500, 6, true, false],
            ['Casque supra-auriculaire', 'Audio & écoute', 'JBL', 24500, 7, false, false],
            ['Ampoule Wi-Fi E27', 'Smart Home', 'TP-Link', 5500, 30, false, true],
            ['Prise connectée 16A', 'Smart Home', 'TP-Link', 7500, 16, false, false],
            ['Caméra indoor 2K', 'Smart Home', 'Xiaomi', 32500, 5, true, false],
            ['Sonnette vidéo Wi-Fi', 'Smart Home', 'TP-Link', 38500, 4, false, false],
            ['Coque MagSafe iPhone', 'Accessoires smartphone', 'Belkin', 8900, 20, false, false],
            ['Film verre trempé 2 pièces', 'Accessoires smartphone', 'OVL Tech', 2500, 50, false, true],
            ['Support voiture grille aération', 'Accessoires automobiles', 'Baseus', 4500, 28, false, true],
            ['Organiseur coffre auto', 'Accessoires automobiles', 'OVL Tech', 9900, 11, false, false],
            ['Trépied smartphone 1.6m', 'Création de contenu', 'OVL Tech', 8900, 13, false, false],
            ['Ring light 26 cm', 'Création de contenu', 'OVL Tech', 11500, 9, true, false],
            ['Micro cravate USB-C', 'Création de contenu', 'OVL Tech', 13500, 8, false, false],
            ['Perche selfie Bluetooth', 'Création de contenu', 'Oraimo', 5500, 17, false, false],
            ['Lampe de bureau LED', 'Gadgets utiles', 'Xiaomi', 16500, 10, false, false],
            ['Mini ventilateur USB', 'Gadgets utiles', 'Baseus', 6500, 21, false, true],
            ['Détecteur de fumée simple', 'Gadgets utiles', 'OVL Tech', 7900, 12, false, false],
            ['Nettoyeur clavier kit', 'Gadgets utiles', 'OVL Tech', 3500, 24, false, false],
            ['SSD portable 1 To', 'Accessoires PC', 'Samsung', 69500, 3, true, false],
            ['Chargeur sans fil 15W', 'Accessoires smartphone', 'Anker', 12500, 0, false, false],
            ['Adaptateur USB-C vers HDMI', 'Câbles & connectique', 'Ugreen', 7900, 14, false, false],
        ];

        foreach ($products as $index => [$name, $categoryName, $brandName, $price, $stock, $featured, $isNew]) {
            $sku = 'OVL-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $slug = Str::slug($name);

            $resolvedBrand = $brand(Str::slug($brandName));
            if (! $resolvedBrand) {
                $resolvedBrand = Brand::query()->firstOrCreate(
                    ['slug' => Str::slug($brandName)],
                    ['name' => $brandName, 'status' => Status::Active]
                );
            }

            Product::query()->firstOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $bySlug($categoryName)?->id,
                    'brand_id' => $resolvedBrand->id,
                    'name' => $name,
                    'slug' => $slug,
                    'short_description' => $name.' — disponible chez OVL Tech, Abidjan.',
                    'description' => $name.' pour un usage quotidien. Paiement à la livraison en Côte d’Ivoire.',
                    'purchase_price' => (int) round($price * 0.62),
                    'selling_price' => $price,
                    'compare_price' => $featured && $price > 0 ? (int) round($price * 1.18) : null,
                    'cost_price' => (int) round($price * 0.58),
                    'stock_quantity' => $stock,
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                    'status' => Status::Active,
                    'featured' => $featured,
                    'is_new' => $isNew,
                    'is_best_seller' => $featured,
                    'meta_title' => $name.' | OVL Tech',
                    'meta_description' => 'Achetez '.$name.' en FCFA, livraison Abidjan.',
                ]
            );
        }
    }
}
