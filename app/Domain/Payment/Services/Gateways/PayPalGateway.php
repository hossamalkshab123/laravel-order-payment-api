<?php

namespace App\Domain\Payment\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Domain\Payment\Enums\PaymentStatus;

class PayPalGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'paypal';
    }

    public function supports(string $method): bool
    {
        return $method === 'paypal';
    }

    public function process(array $payload): array
    {
        return [
            'status' => PaymentStatus::SUCCESSFUL->value,
            'payment_id' => 'pp_'.uniqid(),
            'reference' => 'paypal-'.uniqid(),
        ];
    }
}
