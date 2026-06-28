<?php

namespace App\Http\Controllers\Api;

use App\Core\Base\BaseController;
use App\Core\Exceptions\OrderException;
use App\Domain\Order\DTOs\OrderDTO;
use App\Domain\Order\Requests\CreateOrderRequest;
use App\Domain\Order\Requests\OrderFilterRequest;
use App\Domain\Order\Requests\UpdateOrderRequest;
use App\Domain\Order\Resources\OrderResource;
use App\Domain\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
{
    public function __construct(protected OrderService $service) {}

    public function index(OrderFilterRequest $request): JsonResponse
    {
        $orders = $this->service->listOrders([
            'status' => $request->query('status'),
            'user_id' => Auth::id(),
        ]);

        return $this->successResponse(
            OrderResource::collection($orders),
            'Orders retrieved successfully.'
        );
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->service->createOrder(OrderDTO::fromArray([
            'customer_name' => $request->input('customer_name'),
            'email' => $request->input('email'),
            'currency' => $request->input('currency'),
            'items' => $request->input('items'),
            'user_id' => Auth::id(),
        ]));

        return $this->successResponse(new OrderResource($order), 'Order created successfully.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->service->findOrder($id);

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->service->findOrder($id);

        try {
            $order = $this->service->updateOrder($order, $request->validated());
        } catch (OrderException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(new OrderResource($order), 'Order updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $order = $this->service->findOrder($id);

        try {
            $this->service->deleteOrder($order);
        } catch (OrderException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(null, 'Order deleted successfully.');
    }
}
