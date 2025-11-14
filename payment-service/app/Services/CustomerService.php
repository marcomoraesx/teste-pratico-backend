<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CustomerService
{
    public function detail(int $customer_id): Customer
    {
        $customer = Customer::with('purchases')->find($customer_id);
        if (!$customer) throw new BadRequestException('Customer not found.');
        return $customer;
    }

    public function list(int $per_page, string $order): LengthAwarePaginator
    {
        return Customer::query()
            ->orderBy('id', $order)
            ->paginate($per_page);
    }
}
