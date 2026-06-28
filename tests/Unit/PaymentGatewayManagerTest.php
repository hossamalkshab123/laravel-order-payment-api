<?php

namespace Tests\Unit;

use App\Domain\Payment\Services\Gateways\CreditCardGateway;
use App\Domain\Payment\Services\Gateways\PaymentGatewayManager;
use App\Core\Exceptions\PaymentException;
use PHPUnit\Framework\TestCase;

class PaymentGatewayManagerTest extends TestCase
{
    public function test_it_resolves_registered_gateways(): void
    {
        $manager = new PaymentGatewayManager();
        $gateway = new CreditCardGateway();

        $manager->register('credit_card', $gateway);

        $resolved = $manager->resolve('credit_card');

        $this->assertSame('credit_card', $resolved->name());
        $this->assertTrue($resolved->supports('credit_card'));
    }

    public function test_it_throws_when_gateway_is_not_supported(): void
    {
        $this->expectException(PaymentException::class);

        $manager = new PaymentGatewayManager();

        $manager->resolve('unknown_gateway');
    }
}
