<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $customers = User::query()
            ->where('role', 'customer')
            ->withCount('orders')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);
        $this->authorize('view', $customer);

        $customer->load(['profile', 'addresses']);
        $orders = $customer->orders()->withCount('items')->latest()->paginate(10);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function update(Request $request, User $customer, AuditLogger $audit): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        $this->authorize('update', $customer);

        $status = Status::from($request->validate([
            'status' => ['required', Rule::enum(Status::class)],
        ])['status']);

        $previous = $customer->status;
        $customer->forceFill(['status' => $status])->save();

        $audit->record(
            'Statut client',
            'customers',
            $customer,
            ['status' => $previous->value],
            ['status' => $status->value],
        );

        return back()->with('status', 'Client mis à jour.');
    }
}
