<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\Storefront\CartService;
use App\Services\Storefront\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->authorize('view', $order);

        return new OrderResource($order->load('items'));
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $cart = $this->carts->findFromRequest($request);

        $order = $this->checkout->place($request->validated(), $cart);

        return (new OrderResource($order->load('items')))
            ->response()
            ->setStatusCode(201);
    }
}
