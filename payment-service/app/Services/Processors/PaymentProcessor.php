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

    public function process($payment): array
    {
        foreach ($this->gateways as $gateway) {
            try {
                $gateway_name = $gateway->get_name();
                $payment_result = $gateway->process($payment);
                if ($payment_result['status']) {
                    Log::info("Payment successful via {$gateway_name}");
                    $gateway_class_name = $gateway->get_class_name();
                    return [
                        $gateway_class_name,
                        $payment_result['external_transaction_id']
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("Gateway {$gateway_name} failed: {$e->getMessage()}");
            }
        }
        throw new Exception('All gateways failed to process the payment', 500);
    }
}
