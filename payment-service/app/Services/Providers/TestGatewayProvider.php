<?php

namespace App\Services\Providers;

use App\Contracts\PaymentGatewayInterface;

class TestGatewayProvider implements PaymentGatewayInterface
{
    public function authenticate(): void {}

    public function process($payment): array
    {
        return ['status' => true, 'external_transaction_id' => 'test123'];
    }

    public function refund(string $transaction_id): bool
    {
        return true;
    }

    public function get_class_name(): string
    {
        return class_basename(TestGatewayProvider::class);
    }

    public function get_name(): string
    {
        return 'Test';
    }
}
