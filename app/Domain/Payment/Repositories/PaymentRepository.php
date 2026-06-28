<?php

namespace App\Domain\Payment\Repositories;

use App\Core\Base\BaseRepository;
use App\Domain\Payment\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function paginate(): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('order')->latest()->paginate(15);
    }

    public function paginateForUser(int $userId): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereHas('order', fn($query) => $query->where('user_id', $userId))
            ->with('order')
            ->latest()
            ->paginate(15);
    }

    public function forOrder(int $orderId)
    {
        return $this->model->newQuery()->where('order_id', $orderId)->with('order')->latest()->paginate(15);
    }

    public function forOrderOwnedBy(int $orderId, int $userId)
    {
        return $this->model->newQuery()
            ->where('order_id', $orderId)
            ->whereHas('order', fn($query) => $query->where('user_id', $userId))
            ->with('order')
            ->latest()
            ->paginate(15);
    }

    public function createFromArray(array $data): Payment
    {
        return $this->model->create($data);
    }
}
