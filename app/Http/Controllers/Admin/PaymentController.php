<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with('order')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('reference', 'like', $term)
                        ->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);
        $payment->load('order.items');

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }

    public function update(Request $request, Payment $payment, PaymentService $payments, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('update', $payment);

        $status = PaymentStatus::from($request->validate([
            'status' => ['required', 'in:paid,failed'],
        ])['status']);
        $previous = $payment->status;

        if ($status === PaymentStatus::Paid) {
            $payments->markPaid($payment);
        } else {
            $payments->markFailed($payment);
        }

        $audit->record(
            'Paiement mis à jour',
            'payments',
            $payment,
            ['status' => $previous->value],
            ['status' => $status->value],
        );

        return back()->with('status', 'Paiement mis à jour.');
    }
}
