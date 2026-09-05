<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use App\Services\Delivery\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Delivery::class);

        $deliveries = Delivery::query()
            ->with('order')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('tracking_number', 'like', $term)
                        ->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(Delivery $delivery): View
    {
        $this->authorize('view', $delivery);
        $delivery->load(['order.items', 'assignee']);

        return view('admin.deliveries.show', [
            'delivery' => $delivery,
            'staff' => User::query()->whereIn('role', [UserRole::Admin, UserRole::Manager])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Delivery $delivery, DeliveryService $deliveries): RedirectResponse
    {
        $this->authorize('update', $delivery);

        $data = $request->validate([
            'status' => ['required', Rule::enum(DeliveryStatus::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'failure_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $deliveries->updateStatus(
            $delivery,
            DeliveryStatus::from($data['status']),
            isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            $data['failure_reason'] ?? null,
        );

        return back()->with('status', 'Livraison mise à jour.');
    }
}
