<?php

namespace App\Domain\Payment\Resources;

use App\Core\Base\BaseResource;

class PaymentResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_id' => $this->payment_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'gateway' => $this->gateway,
            'reference' => $this->reference,
        ];
    }
}
