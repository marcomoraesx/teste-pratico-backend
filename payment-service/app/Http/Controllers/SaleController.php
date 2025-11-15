<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListSalesRequest;
use App\Http\Requests\RegisterSaleRequest;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    private SaleService $sale_service;

    function __construct(SaleService $sale_service)
    {
        $this->sale_service = $sale_service;
    }

    function register(RegisterSaleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sale = $this->sale_service->register($data);
        return response()->json([
            'message' => 'Registration successful',
            'sale' => $sale
        ], 201);
    }

    function detail(string $sale_id): JsonResponse
    {
        $sale = $this->sale_service->detail(intval($sale_id));
        return response()->json([
            'sale' => $sale,
        ], 200);
    }

    function list(ListSalesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $per_page = $data['per_page'] ?? 15;
        $order = $data['order'] ?? 'asc';
        $sales = $this->sale_service->list($per_page, $order);
        return response()->json([
            'sales' => $sales
        ], 200);
    }

    function refund(string $sale_id): JsonResponse
    {
        $this->sale_service->refund(intval($sale_id));
        return response()->json([
            'message' => 'Refunded successful'
        ], 200);
    }
}
