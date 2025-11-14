<?php

namespace App\Services\Processors;

use Exception;
use Illuminate\Support\Facades\Log;

class PaymentProcessor
{
    /** @var PaymentGatewayInterface[] */
    private array $gateways;

    public function __construct(array $gateways)
    {
        $this->gateways = $gateways;
    }

    public function process($payment): string
    {
        foreach ($this->gateways as $gateway) {
            try {
                $gateway_name = $gateway->getName();
                if ($gateway->process($payment)) {
                    Log::info("Payment successful via {$gateway_name}");
                    return $gateway_name;
                }
            } catch (\Throwable $e) {
                Log::warning("Gateway {$gateway_name} failed: {$e->getMessage()}");
            }
        }
        throw new Exception('All gateways failed to process the payment', 500);
    }
}
