<?php

namespace App\Services\Providers;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class MercadoPagoGatewayProvider implements PaymentGatewayInterface
{
    private static string $base_url;
    private static string $auth_token;
    private static string $auth_secret;

    public function __construct()
    {
        self::$base_url = config('services.mercadopago.base_url');
        self::$auth_token = config('services.mercadopago.auth_token');
        self::$auth_secret = config('services.mercadopago.auth_secret');
    }

    public function authenticate(): void {}

    public function process($payment): array
    {
        $response = Http::withHeaders([
            'Gateway-Auth-Token' => self::$auth_token,
            'Gateway-Auth-Secret' => self::$auth_secret
        ])->post(
            self::$base_url . '/transacoes',
            [
                'valor' => intval(round($payment['total_amount'] * 100)),
                'nome' => $payment['name'],
                'email' => $payment['email'],
                'numeroCartao' => $payment['card_number'],
                'cvv' => $payment['cvv']
            ]
        );
        return ['status' => $response->status() === 201, 'external_transaction_id' => $response['id']];
    }

    public function refund(string $transaction_id): bool
    {
        $response = Http::withHeaders([
            'Gateway-Auth-Token' => self::$auth_token,
            'Gateway-Auth-Secret' => self::$auth_secret
        ])->post(
            self::$base_url . "/transacoes/reembolso",
            [
                'id' => $transaction_id
            ]
        );
        return $response->status() === 201;
    }

    public function get_class_name(): string
    {
        return class_basename(MercadoPagoGatewayProvider::class);
    }

    public function get_name(): string
    {
        return 'Mercado Pago';
    }
}
