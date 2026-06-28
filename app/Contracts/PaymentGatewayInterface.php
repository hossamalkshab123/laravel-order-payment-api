<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function name(): string;

    public function supports(string $method): bool;

    public function process(array $payload): array;
}
