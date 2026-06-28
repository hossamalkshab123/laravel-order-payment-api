<?php

namespace App\Domain\Order\Resources;

use App\Core\Base\BaseResource;

class OrderResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'customer_name' => $this->customer_name,
            'email' => $this->email,
            'currency' => $this->currency,
            'status' => $this->status,
            'total' => (float) $this->total,
            'items' => $this->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ])->values(),
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'payment_id' => $payment->payment_id,
                    'status' => $payment->status,
                    'amount' => (float) $payment->amount,
                ])->values();
            }),
        ];
    }
}
