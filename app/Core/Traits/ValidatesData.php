<?php

namespace App\Core\Traits;

trait ValidatesData
{
    protected function normalizeItems(array $items): array
    {
        return array_map(function (array $item): array {
            return [
                'product_name' => $item['product_name'] ?? $item['sku'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? $item['unit_price'] ?? 0),
            ];
        }, $items);
    }
}
