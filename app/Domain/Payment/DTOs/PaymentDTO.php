<?php

namespace App\Domain\Payment\DTOs;

class PaymentDTO
{
    public function __construct(
        public readonly int $orderId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $paymentMethod,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: (int) $data['order_id'],
            amount: (float) $data['amount'],
            currency: $data['currency'],
            paymentMethod: $data['payment_method'] ?? $data['provider'] ?? 'credit_card',
        );
    }
}
