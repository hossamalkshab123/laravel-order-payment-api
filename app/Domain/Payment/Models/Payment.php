<?php

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'gateway',
        'reference',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
