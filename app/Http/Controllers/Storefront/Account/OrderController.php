<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('storefront.account.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->load('items');

        return view('storefront.account.orders.show', [
            'order' => $order,
        ]);
    }

    public function invoice(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->load('items');

        return view('storefront.account.orders.invoice', [
            'order' => $order,
        ]);
    }
}
