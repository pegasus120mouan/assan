<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AuditLogger;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('order_number', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('customer_phone', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['items.product', 'payments', 'delivery', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, Order $order, StockService $stock, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $status = OrderStatus::from($data['status']);
        $previous = $order->status;

        if ($status === OrderStatus::Cancelled && $order->status !== OrderStatus::Cancelled) {
            $order->load('items.product', 'items.variant');

            foreach ($order->items as $item) {
                if ($item->product) {
                    $stock->release($item->product, $item->quantity, $item->variant);
                }
            }

            $order->update([
                'status' => $status,
                'cancelled_at' => now(),
            ]);

            $audit->record('Commande annulée', 'orders', $order, ['status' => $previous->value], ['status' => $status->value]);
            OrderStatusChanged::dispatch($order->fresh() ?? $order, $previous, $status);

            return back()->with('status', 'Commande annulée. Le stock réservé a été libéré.');
        }

        $order->update([
            'status' => $status,
            'confirmed_at' => $status === OrderStatus::Confirmed ? ($order->confirmed_at ?? now()) : $order->confirmed_at,
        ]);

        $audit->record('Statut commande', 'orders', $order, ['status' => $previous->value], ['status' => $status->value]);
        OrderStatusChanged::dispatch($order->fresh() ?? $order, $previous, $status);

        return back()->with('status', 'Statut de la commande mis à jour.');
    }
}
