<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function stats(?CarbonInterface $now = null): array
    {
        $now ??= now();
        $today = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();

        return [
            'today' => [
                'revenue' => $this->revenueBetween($today, $now),
                'orders' => Order::query()->whereBetween('created_at', [$today, $now])->count(),
                'products_sold' => $this->productsSoldBetween($today, $now),
                'new_customers' => User::query()
                    ->where('role', UserRole::Customer)
                    ->whereBetween('created_at', [$today, $now])
                    ->count(),
                'pending_orders' => Order::query()->pending()->count(),
            ],
            'revenue' => [
                'week' => $this->revenueBetween($weekStart, $now),
                'month' => $this->revenueBetween($monthStart, $now),
            ],
            'daily_revenue' => $this->dailyRevenue($sevenDaysAgo, $now),
            'sales_by_category' => $this->salesByCategory($monthStart, $now),
            'top_products' => $this->topProducts($monthStart, $now),
            'orders_by_status' => $this->ordersByStatus(),
            'recent_orders' => Order::query()
                ->withCount('items')
                ->latest()
                ->limit(8)
                ->get(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function chartPayload(array $stats): array
    {
        return [
            'daily' => [
                'labels' => array_column($stats['daily_revenue'], 'label'),
                'values' => array_column($stats['daily_revenue'], 'revenue'),
            ],
            'categories' => [
                'labels' => $stats['sales_by_category']->pluck('name')->all(),
                'values' => $stats['sales_by_category']->pluck('total')->all(),
            ],
            'statuses' => [
                'labels' => collect($stats['orders_by_status'])->pluck('label')->all(),
                'values' => collect($stats['orders_by_status'])->pluck('total')->all(),
            ],
        ];
    }

    private function revenueBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) Order::query()
            ->countedAsRevenue()
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');
    }

    private function productsSoldBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) OrderItem::query()
            ->whereHas('order', function ($query) use ($from, $to): void {
                $query->notCancelled()->whereBetween('created_at', [$from, $to]);
            })
            ->sum('quantity');
    }

    /**
     * @return list<array{date: string, label: string, revenue: int}>
     */
    private function dailyRevenue(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Order::query()
            ->countedAsRevenue()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->mapWithKeys(function ($row): array {
                $day = \Carbon\Carbon::parse($row->day)->toDateString();

                return [$day => (int) $row->revenue];
            });

        $days = [];
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $days[] = [
                'date' => $key,
                'label' => $cursor->translatedFormat('D d/m'),
                'revenue' => (int) ($rows[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return $days;
    }

    private function salesByCategory(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value])
            ->whereBetween('orders.created_at', [$from, $to])
            ->select('categories.name', DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->name,
                'total' => (int) $row->total,
            ]);
    }

    private function topProducts(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value])
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'order_items.product_name',
                'order_items.sku',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            )
            ->groupBy('order_items.product_name', 'order_items.sku')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->product_name,
                'sku' => $row->sku,
                'quantity' => (int) $row->quantity_sold,
                'revenue' => (int) $row->revenue,
            ]);
    }

    /**
     * @return list<array{status: string, label: string, total: int}>
     */
    private function ordersByStatus(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(OrderStatus::cases())
            ->map(fn (OrderStatus $status): array => [
                'status' => $status->value,
                'label' => $status->label(),
                'total' => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }
}
