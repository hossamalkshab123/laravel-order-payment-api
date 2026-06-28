<?php

namespace App\Domain\Order\Repositories;

use App\Core\Base\BaseRepository;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function queryWithFilters(array $filters = []): Builder
    {
        $query = $this->model->newQuery()->with(['items', 'payments']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->queryWithFilters($filters)->latest()->paginate(15);
    }

    public function createFromArray(array $data): Order
    {
        return $this->model->create($data);
    }
}
