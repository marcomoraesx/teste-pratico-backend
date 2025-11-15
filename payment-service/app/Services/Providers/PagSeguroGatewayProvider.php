<?php

namespace App\Services\Providers;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class PagSeguroGatewayProvider implements PaymentGatewayInterface
{
    private $token;
    private static string $base_url;
    private static string $auth_email;
    private static string $auth_token;

    public function __construct()
    {
        self::$base_url = config('services.pagseguro.base_url');
        self::$auth_email = config('services.pagseguro.auth_email');
        self::$auth_token = config('services.pagseguro.auth_token');
    }

    public function authenticate(): void
    {
        $response = Http::post(
            self::$base_url . '/login',
            [
                'email' => self::$auth_email,
                'token' => self::$auth_token
            ]
        );
        $this->token = $response['token'];
    }

    public function process($payment): array
    {
        if (!$this->token) $this->authenticate();
        $response = Http::withToken($this->token)->post(
            self::$base_url . '/transactions',
            [
                'amount' => intval(round($payment['total_amount'] * 100)),
                'name' => $payment['name'],
                'email' => $payment['email'],
                'cardNumber' => $payment['card_number'],
                'cvv' => $payment['cvv']
            ]
        );
        if ($response->status() === 401) {
            $this->authenticate();
            return $this->process($payment);
        }
        return ['status' => $response->status() === 201, 'external_transaction_id' => $response['id']];
    }

    public function refund(string $transaction_id): bool
    {
        if (!$this->token) $this->authenticate();
        $response = Http::withToken($this->token)->post(
            self::$base_url . "/transactions/{$transaction_id}/charge_back"
        );
        if ($response->status() === 401) {
            $this->authenticate();
            return $this->refund($transaction_id);
        }
        return $response->status() === 201;
    }

    public function get_class_name(): string
    {
        return class_basename(PagSeguroGatewayProvider::class);
    }

    public function get_name(): string
    {
        return 'PagSeguro';
    }
}
