<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\Status;
use App\Models\Customer;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Transaction;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SaleService
{
    private PaymentService $payment_service;

    function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }

    public function register($data): Sale
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::find($data['customer_id']);
            if (!$customer) throw new BadRequestException('Customer not found.');
            $product_ids = collect($data['items'])->pluck('product_id')->all();
            $products = Product::whereIn('id', $product_ids)->get();
            $items = [];
            $total_amount = 0;
            $discount_amount = doubleval($data['discount_amount']);
            $net_amount = 0;
            foreach ($products as $product) {
                $item = collect($data['items'])->firstWhere('product_id', $product->id);
                $item_total_amount = $item['quantity'] * $product->price;
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unity_price' => $product->price,
                    'total_amount' => $item_total_amount
                ];
                $total_amount += $item_total_amount;
            }
            if ($discount_amount > $total_amount) throw new BadRequestException('Discount greater than the total purchase price.');
            $net_amount = $total_amount - $discount_amount;
            $payment = $data['payment'];
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'transaction_id' => null,
                'payment_method' => PaymentMethod::from($payment['payment_method']),
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'net_amount' => $net_amount,
                'status' => Status::COMPLETED,
            ]);
            $sale->items()->createMany($items);
            if (in_array(PaymentMethod::from($payment['payment_method']), [PaymentMethod::CREDIT_CARD, PaymentMethod::DEBIT_CARD])) {
                [$gateway_class_name, $external_transaction_id] = $this->payment_service->handle([
                    'total_amount' => $net_amount,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'card_number' => $payment['card_number'],
                    'cvv' => $payment['cvv'],
                ]);
                $gateway = Gateway::where('class_name', $gateway_class_name)->first();
                if (!$gateway) throw new BadRequestException('Gateway not found.');
                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'gateway_id' => $gateway->id,
                    'external_transaction_id' => $external_transaction_id,
                    'status' => Status::COMPLETED,
                    'total_amount' => $net_amount,
                    'last_digits_card' => substr($payment['card_number'], -4)
                ]);
                $sale->update([
                    'transaction_id' => $transaction?->id ?? null,
                ]);
            }
            return $sale;
        });
    }

    public function detail(int $sale_id): Sale
    {
        $sale = Sale::with(['items', 'transaction', 'customer'])->find($sale_id);
        if (!$sale) throw new BadRequestException('Sale not found.');
        return $sale;
    }

    public function list(int $per_page, string $order): LengthAwarePaginator
    {
        return Sale::query()
            ->orderBy('id', $order)
            ->paginate($per_page);
    }

    public function refund(int $sale_id): void
    {
        DB::transaction(function () use ($sale_id) {
            $sale = Sale::with('transaction')->find($sale_id);
            if (!$sale) throw new BadRequestException('Sale not found.');
            if ($sale->status !== Status::COMPLETED) throw new BadRequestException('This sale is non-refundable.');
            $sale->update([
                'status' => Status::REFUNDED
            ]);
            if (in_array($sale->payment_method, [PaymentMethod::CREDIT_CARD, PaymentMethod::DEBIT_CARD])) {
                $transaction = $sale->transaction;
                if (!$transaction || !$transaction->gateway) throw new BadRequestException('This sale does not have a linked transaction.');
                $transaction->update([
                    'status' => Status::REFUNDED
                ]);
                $gateway = $transaction->gateway;
                $implementation_class = "\App\Services\Providers\\{$gateway->class_name}";
                if (!class_exists($implementation_class)) throw new Exception("Implementation for {$gateway->name} not found.");
                $gateway_provider = app($implementation_class);
                $refunded_successful = $gateway_provider->refund($transaction->external_transaction_id);
                if (!$refunded_successful) throw new Exception("The gateway ({$gateway->name}) cannot refund the purchase.");
            }
        });
    }
}
