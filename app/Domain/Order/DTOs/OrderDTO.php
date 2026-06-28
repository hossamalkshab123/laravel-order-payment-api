<?php

namespace App\Domain\Order\DTOs;

class OrderDTO
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $email,
        public readonly string $currency,
        public readonly array $items,
        public readonly ?int $userId = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerName: $data['customer_name'],
            email: $data['email'],
            currency: $data['currency'],
            items: $data['items'] ?? [],
            userId: $data['user_id'] ?? null,
            status: $data['status'] ?? null,
        );
    }
}
