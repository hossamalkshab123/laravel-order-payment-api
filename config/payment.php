<?php

return [

    'default' => env('PAYMENT_GATEWAY_DEFAULT', 'credit_card'),

    'gateways' => [
        'credit_card' => [
            'enabled' => filter_var(env('PAYMENT_GATEWAY_CREDIT_CARD_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'timeout' => (int) env('PAYMENT_GATEWAY_CREDIT_CARD_TIMEOUT', 30),
        ],
        'paypal' => [
            'enabled' => filter_var(env('PAYMENT_GATEWAY_PAYPAL_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET'),
            'timeout' => (int) env('PAYMENT_GATEWAY_PAYPAL_TIMEOUT', 30),
        ],
    ],

];
