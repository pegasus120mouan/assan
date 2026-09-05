<?php

namespace App\Support\Payments;

use App\Enums\PaymentStatus;
use DateTimeInterface;

class PaymentResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly array $metadata = [],
        public readonly ?string $transactionId = null,
        public readonly ?DateTimeInterface $paidAt = null,
        public readonly string $customerMessage = '',
    ) {}
}
