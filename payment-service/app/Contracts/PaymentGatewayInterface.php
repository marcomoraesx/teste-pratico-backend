<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function authenticate(): void;

    public function process($payment): array;

    public function refund(string $transaction_id): bool;

    public function get_class_name(): string;

    public function get_name(): string;
}
