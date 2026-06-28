<?php

namespace App\Domain\Payment\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Core\Exceptions\PaymentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    protected array $gateways = [];

    public function register(string $name, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    public function resolve(string $name): PaymentGatewayInterface
    {
        $normalized = strtolower($name);

        if (! array_key_exists($normalized, $this->gateways)) {
            throw new PaymentException('Unsupported payment gateway: ' . $name);
        }

        return $this->gateways[$normalized];
    }

    public function all(): array
    {
        return $this->gateways;
    }
}
