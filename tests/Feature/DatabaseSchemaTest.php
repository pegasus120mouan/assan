<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, list<string>>
     */
    public static function expectedTables(): array
    {
        return [
            'users' => ['id', 'name', 'email', 'phone', 'password', 'role', 'status', 'email_verified_at'],
            'regions' => ['id', 'name', 'slug', 'status', 'sort_order'],
            'cities' => ['id', 'region_id', 'name', 'slug', 'status'],
            'zones' => ['id', 'city_id', 'name', 'slug', 'status'],
            'communes' => ['id', 'city_id', 'zone_id', 'name', 'slug', 'status'],
            'categories' => ['id', 'parent_id', 'name', 'slug', 'description', 'image', 'icon', 'status', 'sort_order'],
            'brands' => ['id', 'name', 'slug', 'logo', 'description', 'status'],
            'products' => [
                'id', 'category_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description',
                'purchase_price', 'selling_price', 'compare_price', 'cost_price', 'stock_quantity',
                'reserved_quantity', 'low_stock_threshold', 'weight', 'status', 'featured', 'is_new',
                'is_best_seller', 'meta_title', 'meta_description', 'deleted_at',
            ],
            'product_images' => ['id', 'product_id', 'image', 'alt', 'sort_order', 'is_primary'],
            'product_variants' => ['id', 'product_id', 'name', 'sku', 'price', 'stock_quantity', 'reserved_quantity', 'options', 'status'],
            'stock_movements' => [
                'id', 'product_id', 'product_variant_id', 'type', 'quantity',
                'reference_type', 'reference_id', 'reason', 'user_id',
            ],
            'carts' => ['id', 'user_id', 'session_id', 'status', 'last_activity_at', 'abandoned_at'],
            'cart_items' => ['id', 'cart_id', 'product_id', 'product_variant_id', 'quantity', 'unit_price'],
            'wishlists' => ['id', 'user_id', 'product_id'],
            'customer_profiles' => ['id', 'user_id', 'address', 'commune', 'city', 'country', 'notes'],
            'addresses' => [
                'id', 'user_id', 'label', 'recipient_name', 'phone', 'address',
                'commune_id', 'commune', 'city', 'instructions', 'is_default',
            ],
            'coupons' => [
                'id', 'code', 'type', 'value', 'minimum_order_amount', 'maximum_discount',
                'starts_at', 'expires_at', 'usage_limit', 'usage_per_customer', 'status',
            ],
            'coupon_usages' => ['id', 'coupon_id', 'user_id', 'order_id', 'discount_amount'],
            'promotions' => ['id', 'name', 'type', 'discount_type', 'value', 'product_id', 'category_id', 'starts_at', 'expires_at', 'status'],
            'orders' => [
                'id', 'order_number', 'user_id', 'status', 'payment_status', 'delivery_status',
                'subtotal', 'discount_amount', 'delivery_fee', 'total', 'payment_method',
                'delivery_method', 'customer_name', 'customer_phone', 'delivery_address',
                'delivery_commune', 'delivery_city', 'customer_notes',
            ],
            'order_items' => [
                'id', 'order_id', 'product_id', 'product_variant_id', 'product_name',
                'sku', 'quantity', 'unit_price', 'subtotal',
            ],
            'payments' => ['id', 'order_id', 'reference', 'gateway', 'amount', 'status', 'transaction_id', 'metadata', 'paid_at'],
            'deliveries' => [
                'id', 'order_id', 'tracking_number', 'provider', 'status', 'assigned_to',
                'delivery_fee', 'picked_up_at', 'delivered_at', 'failure_reason', 'metadata',
            ],
            'delivery_fees' => [
                'id', 'city_id', 'commune_id', 'zone_id', 'delivery_method',
                'min_order_amount', 'fee', 'free_above_amount', 'status',
            ],
            'reviews' => ['id', 'user_id', 'product_id', 'order_id', 'rating', 'title', 'comment', 'status'],
            'audit_logs' => ['id', 'user_id', 'action', 'module', 'old_values', 'new_values', 'ip_address', 'user_agent'],
            'notifications' => ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'],
            'settings' => ['id', 'key', 'value', 'type', 'group'],
            'personal_access_tokens' => ['id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities'],
        ];
    }

    public function test_all_shop_tables_and_columns_exist(): void
    {
        foreach (self::expectedTables() as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Table [{$table}] is missing.");
            $this->assertTrue(
                Schema::hasColumns($table, $columns),
                "Table [{$table}] is missing expected columns."
            );
        }
    }
}
