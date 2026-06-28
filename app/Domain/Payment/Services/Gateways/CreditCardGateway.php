<?php

namespace App\Domain\Payment\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Domain\Payment\Enums\PaymentStatus;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'credit_card';
    }

    public function supports(string $method): bool
    {
        return in_array($method, ['credit_card', 'stripe'], true);
    }

    public function process(array $payload): array
    {
        return [
            'status' => PaymentStatus::SUCCESSFUL->value,
            'payment_id' => 'cc_'.uniqid(),
            'reference' => 'card-'.uniqid(),
        ];
    }
}
