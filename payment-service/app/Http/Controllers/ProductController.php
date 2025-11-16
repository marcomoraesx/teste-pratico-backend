<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListProductsRequest;
use App\Http\Requests\RegisterProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    private ProductService $product_service;

    function __construct(ProductService $product_service)
    {
        $this->product_service = $product_service;
    }

    function register(RegisterProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $product = $this->product_service->register($data);
        return response()->json([
            'message' => 'Registration successful',
            'product' => $product,
        ], 201);
    }

    function view(string $product_id): JsonResponse
    {
        $product = $this->product_service->view(intval($product_id));
        return response()->json([
            'product' => $product,
        ], 200);
    }

    function list(ListProductsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $per_page = $data['per_page'] ?? 15;
        $order = $data['order'] ?? 'asc';
        $products = $this->product_service->list($per_page, $order);
        return response()->json([
            'products' => $products
        ], 200);
    }

    function update(string $product_id, UpdateProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->product_service->update(intval($product_id), $data);
        return response()->json([
            'message' => 'Updated successful',
        ], 200);
    }

    function delete(string $product_id): JsonResponse
    {
        $this->product_service->delete(intval($product_id));
        return response()->json([
            'message' => 'Deleted successful',
        ], 200);
    }
}
