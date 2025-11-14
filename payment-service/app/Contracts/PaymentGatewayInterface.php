<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function authenticate(): void;

    public function process($payment): bool;

    public function getName(): string;
}
