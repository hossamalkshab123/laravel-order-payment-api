<?php

namespace App\Http\Controllers\Api;

use App\Core\Base\BaseController;
use App\Core\Exceptions\PaymentException;
use App\Domain\Payment\DTOs\PaymentDTO;
use App\Domain\Payment\Requests\ProcessPaymentRequest;
use App\Domain\Payment\Resources\PaymentResource;
use App\Domain\Payment\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends BaseController
{
    public function __construct(protected PaymentService $service) {}

    public function process(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->service->processPayment(PaymentDTO::fromArray($request->validated()));
        } catch (PaymentException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(new PaymentResource($payment), 'Payment processed successfully.', 201);
    }

    public function index(): JsonResponse
    {
        $payments = $this->service->listPayments();

        return $this->successResponse(PaymentResource::collection($payments), 'Payments retrieved successfully.');
    }

    public function byOrder(int $orderId): JsonResponse
    {
        $payments = $this->service->paymentsForOrder($orderId);

        return $this->successResponse(PaymentResource::collection($payments), 'Order payments retrieved successfully.');
    }
}
