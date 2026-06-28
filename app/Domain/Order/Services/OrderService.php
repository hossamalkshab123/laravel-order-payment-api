<?php

namespace App\Domain\Order\Services;

use App\Core\Base\BaseService;
use App\Core\Exceptions\OrderException;
use App\Domain\Order\DTOs\OrderDTO;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{
    public function __construct(protected OrderRepository $repository) {}

    public function createOrder(OrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto): Order {
            $order = $this->repository->createFromArray([
                'user_id' => $dto->userId,
                'customer_name' => $dto->customerName,
                'email' => $dto->email,
                'currency' => $dto->currency,
                'status' => OrderStatus::PENDING->value,
                'total' => $this->calculateTotal($dto->items),
            ]);

            foreach ($dto->items as $item) {
                $order->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                ]);
            }

            return $order->load('items');
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data): Order {
            $newStatus = $data['status'] ?? null;

            // Validate status transitions
            if ($newStatus !== null && ! $this->canTransition($order->status, $newStatus)) {
                throw new OrderException('Order status transition is not allowed.');
            }

            // Update status if provided
            if ($newStatus !== null) {
                $order->status = $newStatus;
            }

            // Update customer details only for pending orders
            if (isset($data['customer_name']) || isset($data['email'])) {
                if ($order->status !== OrderStatus::PENDING->value) {
                    throw new OrderException('Customer details can only be updated for pending orders.');
                }

                if (isset($data['customer_name'])) {
                    $order->customer_name = $data['customer_name'];
                }

                if (isset($data['email'])) {
                    $order->email = $data['email'];
                }
            }

            // Update order items if provided (only for pending orders)
            if (isset($data['items']) && is_array($data['items'])) {
                if ($order->status !== OrderStatus::PENDING->value) {
                    throw new OrderException('Order items can only be updated for pending orders.');
                }

                // Delete existing items
                $order->items()->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    $order->items()->create([
                        'product_name' => $item['product_name'],
                        'quantity' => (int) $item['quantity'],
                        'price' => (float) $item['price'],
                    ]);
                }

                // Recalculate total
                $order->total = $this->calculateTotal($data['items']);
            }

            $order->save();

            return $order->fresh(['items', 'payments']);
        });
    }

    public function deleteOrder(Order $order): bool
    {
        if ($order->payments()->exists()) {
            throw new OrderException('Orders with payments cannot be deleted.');
        }

        return (bool) $order->delete();
    }

    public function findOrder(int $id): Order
    {
        return $this->repository->getModel()->newQuery()->with(['items', 'payments'])->findOrFail($id);
    }

    public function listOrders(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    private function calculateTotal(array $items): float
    {
        return round(array_reduce($items, static fn(float $carry, array $item): float => $carry + ((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)), 0.0), 2);
    }

    private function canTransition(string $currentStatus, string $newStatus): bool
    {
        $flow = [
            OrderStatus::PENDING->value => [OrderStatus::CONFIRMED->value, OrderStatus::CANCELLED->value],
            OrderStatus::CONFIRMED->value => [OrderStatus::CANCELLED->value],
            OrderStatus::CANCELLED->value => [],
        ];

        return in_array($newStatus, $flow[$currentStatus] ?? [], true);
    }
}
