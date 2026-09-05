<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-22 14:00:00', 'Africa/Abidjan'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_dashboard_shows_zero_metrics_without_orders(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Aujourd\'hui', false)
            ->assertSee('0 FCFA', false)
            ->assertSee('Commandes en attente', false);
    }

    public function test_dashboard_counts_today_revenue_orders_and_pending(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $paid = Order::factory()->for($customer)->paid()->create([
            'total' => 25000,
            'created_at' => now(),
        ]);
        OrderItem::factory()->for($paid)->create([
            'quantity' => 2,
            'unit_price' => 12500,
            'subtotal' => 25000,
            'product_name' => 'Powerbank 20 000 mAh',
            'sku' => 'PB-20000',
        ]);

        Order::factory()->for($customer)->pending()->create([
            'total' => 8000,
            'created_at' => now(),
        ]);

        Order::factory()->for($customer)->cancelled()->create([
            'total' => 50000,
            'payment_status' => PaymentStatus::Paid,
            'created_at' => now(),
        ]);

        Order::factory()->for($customer)->paid()->create([
            'total' => 12000,
            'created_at' => now()->subDay(),
        ]);

        $stats = app(DashboardService::class)->stats();

        $this->assertSame(25000, $stats['today']['revenue']);
        $this->assertSame(3, $stats['today']['orders']);
        $this->assertSame(2, $stats['today']['products_sold']);
        $this->assertSame(1, $stats['today']['pending_orders']);
        $this->assertSame(37000, $stats['revenue']['week']);
        $this->assertSame(37000, $stats['revenue']['month']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('25 000 FCFA', false)
            ->assertSee('37 000 FCFA', false);
    }

    public function test_dashboard_counts_new_customers_today(): void
    {
        User::factory()->customer()->create(['created_at' => now()]);
        User::factory()->customer()->create(['created_at' => now()->subDay()]);
        User::factory()->admin()->create(['created_at' => now()]);

        $stats = app(DashboardService::class)->stats();

        $this->assertSame(1, $stats['today']['new_customers']);
    }

    public function test_sales_by_category_and_order_statuses_are_computed(): void
    {
        $category = Category::factory()->create(['name' => 'Audio']);
        $product = Product::factory()->for($category)->create();
        $order = Order::factory()->paid()->create(['created_at' => now()]);
        OrderItem::factory()->for($order)->for($product)->create([
            'quantity' => 3,
        ]);

        $stats = app(DashboardService::class)->stats();

        $this->assertSame('Audio', $stats['sales_by_category']->first()['name']);
        $this->assertSame(3 * $product->selling_price, $stats['sales_by_category']->first()['total']);

        $pending = collect($stats['orders_by_status'])->firstWhere('status', OrderStatus::Pending->value);
        $confirmed = collect($stats['orders_by_status'])->firstWhere('status', OrderStatus::Confirmed->value);

        $this->assertSame(0, $pending['total']);
        $this->assertSame(1, $confirmed['total']);
    }

    public function test_customer_cannot_open_admin_placeholder_modules(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_open_placeholder_modules(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Catégories', false);
    }

    public function test_admin_sidebar_shows_pending_orders_badge(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->pending()->count(2)->create();
        Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('2 commandes en attente', false);
    }
}
