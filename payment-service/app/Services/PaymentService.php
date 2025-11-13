<?php

namespace App\Services;

use App\Services\Processors\PaymentProcessor;

class PaymentService
{
    public function __construct(private PaymentProcessor $processor) {}

    public function handle($payment): string
    {
        return $this->processor->process($payment);
    }
}
