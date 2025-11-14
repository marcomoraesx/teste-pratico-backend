<?php

namespace App\Services\Providers;

use App\Contracts\PaymentGatewayInterface;

class PagSeguroGatewayProvider implements PaymentGatewayInterface
{
    public function __construct() {}

    public function authenticate(): void {}

    public function process($payment): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'PagSeguro';
    }
}
