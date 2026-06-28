<?php

namespace App\Providers;

use App\Domain\Payment\Services\Gateways\CreditCardGateway;
use App\Domain\Payment\Services\Gateways\PaymentGatewayManager;
use App\Domain\Payment\Services\Gateways\PayPalGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function (): PaymentGatewayManager {
            $manager = new PaymentGatewayManager;
            $config = config('payment.gateways', []);

            foreach ($config as $name => $gatewayConfig) {
                if (! ($gatewayConfig['enabled'] ?? false)) {
                    continue;
                }

                $gateway = match ($name) {
                    'credit_card' => new CreditCardGateway,
                    'paypal' => new PayPalGateway,
                    default => null,
                };

                if ($gateway !== null) {
                    $manager->register($name, $gateway);
                }
            }

            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
