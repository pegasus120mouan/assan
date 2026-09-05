<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\City;
use App\Models\Commune;
use App\Models\Coupon;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_helpers_and_customer_relations(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        CustomerProfile::factory()->for($customer)->create(['city' => 'Abidjan']);
        Address::factory()->for($customer)->count(2)->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isStaff());
        $this->assertTrue($customer->isCustomer());
        $this->assertSame(UserRole::Customer, $customer->role);
        $this->assertTrue($customer->profile()->exists());
        $this->assertCount(2, $customer->addresses);
    }

    public function test_category_tree_and_products(): void
    {
        $parent = Category::factory()->create(['name' => 'Audio']);
        $child = Category::factory()->create(['parent_id' => $parent->id, 'name' => 'Écouteurs']);
        $product = Product::factory()->for($child, 'category')->create();

        $this->assertTrue($parent->children->contains($child));
        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($child->products->contains($product));
        $this->assertTrue($product->category->is($child));
    }

    public function test_product_has_brand_images_and_variants(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->for($brand)->create();
        ProductImage::factory()->for($product)->create(['is_primary' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'name' => 'Noir',
            'options' => ['color' => 'Noir'],
        ]);

        $product->load(['brand', 'images', 'variants', 'primaryImage']);

        $this->assertTrue($product->brand->is($brand));
        $this->assertNotNull($product->primaryImage);
        $this->assertTrue($product->variants->contains($variant));
        $this->assertSame(['color' => 'Noir'], $variant->fresh()->options);
    }

    public function test_available_stock_uses_variant_quantities_when_present(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        ProductVariant::factory()->for($product)->create([
            'stock_quantity' => 4,
            'reserved_quantity' => 1,
        ]);
        ProductVariant::factory()->for($product)->create([
            'stock_quantity' => 6,
            'reserved_quantity' => 0,
        ]);

        $this->assertSame(9, $product->fresh()->load('variants')->availableStock());
        $this->assertTrue($product->fresh()->load('variants')->isInStock());
    }

    public function test_available_stock_uses_product_quantity_without_variants(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 8,
            'reserved_quantity' => 4,
            'low_stock_threshold' => 5,
        ]);

        $this->assertSame(4, $product->availableStock());
        $this->assertTrue($product->isLowStock());
    }

    public function test_product_on_sale_exposes_discount_percent(): void
    {
        $product = Product::factory()->create([
            'selling_price' => 8000,
            'compare_price' => 10000,
        ]);

        $this->assertTrue($product->isOnSale());
        $this->assertSame(20, $product->discountPercent());
    }

    public function test_cart_subtotal_is_calculated_from_items(): void
    {
        $cart = Cart::factory()->guest()->create();
        $product = Product::factory()->inStock()->create(['selling_price' => 5000]);

        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 2,
            'unit_price' => 5000,
        ]);
        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 1,
            'unit_price' => 3000,
        ]);

        $this->assertSame(13000, $cart->fresh()->load('items')->subtotal());
    }

    public function test_order_items_keep_a_price_and_name_snapshot(): void
    {
        $product = Product::factory()->create([
            'name' => 'Powerbank 20 000 mAh',
            'sku' => 'PB-20000',
            'selling_price' => 15000,
        ]);
        $order = Order::factory()->create();

        $item = OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => 'Powerbank 20 000 mAh',
            'sku' => 'PB-20000',
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $product->update(['selling_price' => 20000, 'name' => 'Nouveau nom']);

        $this->assertSame('Powerbank 20 000 mAh', $item->fresh()->product_name);
        $this->assertSame(15000, $item->fresh()->unit_price);
        $this->assertTrue($order->items->contains($item));
    }

    public function test_expired_coupon_is_not_usable(): void
    {
        $coupon = Coupon::factory()->expired()->create(['status' => Status::Active]);

        $this->assertTrue($coupon->isExpired());
        $this->assertFalse($coupon->isUsable());
    }

    public function test_geography_relations(): void
    {
        $region = Region::factory()->create(['name' => 'Lagunes']);
        $city = City::factory()->for($region)->create(['name' => 'Abidjan']);
        $commune = Commune::factory()->for($city)->create(['name' => 'Cocody']);

        $this->assertTrue($region->cities->contains($city));
        $this->assertTrue($city->communes->contains($commune));
        $this->assertTrue($commune->city->is($city));
    }
}
