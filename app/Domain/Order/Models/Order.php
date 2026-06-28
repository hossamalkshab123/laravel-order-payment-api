<?php

namespace App\Domain\Order\Models;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_name',
        'email',
        'currency',
        'status',
        'total',
    ];

    protected $casts = [
        'total' => 'float',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING->value;
    }

    public function isConfirmed(): bool
    {
        return $this->status === OrderStatus::CONFIRMED->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED->value;
    }
}
