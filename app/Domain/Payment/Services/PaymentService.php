<?php

namespace App\Domain\Payment\Services;

use App\Core\Base\BaseService;
use App\Core\Exceptions\PaymentException;
use App\Core\Traits\HasPermissions;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\DTOs\PaymentDTO;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Repositories\PaymentRepository;
use App\Domain\Payment\Services\Gateways\PaymentGatewayManager;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService extends BaseService
{
    use HasPermissions;
    public function __construct(
        protected PaymentRepository $repository,
        protected PaymentGatewayManager $gatewayManager,
    ) {}

    public function processPayment(PaymentDTO $dto): Payment
    {
        return DB::transaction(function () use ($dto): Payment {
            $order = Order::query()->findOrFail($dto->orderId);

            $this->authorizeOrderAccess($order);

            if ($order->status !== 'confirmed') {
                throw new PaymentException('Payments can only be processed for confirmed orders.');
            }

            if ($order->payments()->where('status', PaymentStatus::SUCCESSFUL->value)->exists()) {
                throw new PaymentException('This order already has a successful payment.');
            }

            if (round($dto->amount, 2) !== round((float) $order->total, 2)) {
                throw new PaymentException('Payment amount must match the order total.');
            }

            $gateway = $this->gatewayManager->resolve($dto->paymentMethod);
            $result = $gateway->process([
                'order_id' => $order->id,
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'payment_method' => $dto->paymentMethod,
            ]);

            $status = $result['status'] ?? PaymentStatus::FAILED->value;

            return $this->repository->createFromArray([
                'order_id' => $order->id,
                'payment_id' => $result['payment_id'] ?? uniqid('pay_'),
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'status' => $status,
                'payment_method' => $dto->paymentMethod,
                'gateway' => $gateway->name(),
                'reference' => $result['reference'] ?? null,
            ]);
        });
    }

    public function listPayments(): mixed
    {
        return $this->repository->paginateForUser(Auth::id());
    }

    public function paymentsForOrder(int $orderId): mixed
    {
        $order = Order::query()->findOrFail($orderId);

        $this->authorizeOrderAccess($order);

        return $this->repository->forOrderOwnedBy($orderId, Auth::id());
    }

    private function authorizeOrderAccess(Order $order): void
    {
        $user = Auth::user();

        if (! $user || ! $this->canAccessResource($order, $user)) {
            throw new AuthorizationException('Unauthorized action. ليس لديك صلاحية للوصول.');
        }
    }
}
