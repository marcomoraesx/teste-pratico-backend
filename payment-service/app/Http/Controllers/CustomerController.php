<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListCustomersRequest;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    private CustomerService $customer_service;

    function __construct(CustomerService $customer_service)
    {
        $this->customer_service = $customer_service;
    }

    function detail(string $customer_id): JsonResponse
    {
        $customer = $this->customer_service->detail(intval($customer_id));
        return response()->json([
            'customer' => $customer,
        ], 200);
    }

    function list(ListCustomersRequest $request): JsonResponse
    {
        $data = $request->validated();
        $per_page = $data['per_page'] ?? 15;
        $order = $data['order'] ?? 'asc';
        $customers = $this->customer_service->list($per_page, $order);
        return response()->json([
            'customers' => $customers
        ], 200);
    }
}
