<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Events\PaymentReceived;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\Gateways\CashOnDeliveryGateway;
use App\Services\Payments\Gateways\MoovMoneyGateway;
use App\Services\Payments\Gateways\MtnMoneyGateway;
use App\Services\Payments\Gateways\OrangeMoneyGateway;
use App\Services\Payments\Gateways\WaveGateway;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /** @var Collection<string, PaymentGatewayInterface> */
    private Collection $gateways;

    public function __construct()
    {
        $this->gateways = collect([
            new CashOnDeliveryGateway,
            new OrangeMoneyGateway,
            new MtnMoneyGateway,
            new MoovMoneyGateway,
            new WaveGateway,
        ])->keyBy(fn (PaymentGatewayInterface $gateway): string => $gateway->key()->value);
    }

    /**
     * @return Collection<int, PaymentGatewayInterface>
     */
    public function enabled(): Collection
    {
        return $this->gateways->values()->filter(
            fn (PaymentGatewayInterface $gateway): bool => $gateway->isEnabled()
        )->values();
    }

    /**
     * @return list<string>
     */
    public function enabledKeys(): array
    {
        return $this->enabled()->map(fn (PaymentGatewayInterface $gateway): string => $gateway->key()->value)->all();
    }

    public function get(PaymentGateway $gateway): PaymentGatewayInterface
    {
        $resolved = $this->gateways->get($gateway->value);

        if (! $resolved) {
            throw ValidationException::withMessages([
                'payment_method' => 'Ce mode de paiement n’est pas disponible.',
            ]);
        }

        return $resolved;
    }

    public function charge(Order $order, PaymentGateway $gateway, int $amount): Payment
    {
        $driver = $this->get($gateway);

        if (! $driver->isEnabled()) {
            throw ValidationException::withMessages([
                'payment_method' => 'Ce mode de paiement n’est pas activé.',
            ]);
        }

        $result = $driver->charge($order, $amount);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'reference' => 'PAY-'.$order->order_number,
            'gateway' => $gateway,
            'amount' => $amount,
            'status' => $result->status,
            'transaction_id' => $result->transactionId,
            'metadata' => array_merge($result->metadata, [
                'customer_message' => $result->customerMessage,
            ]),
            'paid_at' => $result->paidAt,
        ]);

        $order->update([
            'payment_method' => $gateway,
            'payment_status' => $result->status,
        ]);

        return $payment;
    }

    public function markPaid(Payment $payment): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $payment->order?->update([
            'payment_status' => PaymentStatus::Paid,
        ]);

        $payment = $payment->refresh()->load('order');

        if ($payment->order) {
            DB::afterCommit(function () use ($payment): void {
                $fresh = $payment->fresh(['order.user']);
                if ($fresh?->order) {
                    PaymentReceived::dispatch($fresh->order, $fresh);
                }
            });
        }

        return $payment;
    }

    public function markFailed(Payment $payment): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Failed,
        ]);

        $payment->order?->update([
            'payment_status' => PaymentStatus::Failed,
        ]);

        return $payment->refresh();
    }
}
