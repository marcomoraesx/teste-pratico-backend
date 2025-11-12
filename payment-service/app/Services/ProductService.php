<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ProductService
{
    public function register($data): Product
    {
        return DB::transaction(function () use ($data) {
            $product_exists = Product::where('bar_code', $data['bar_code'])->exists();
            if ($product_exists) {
                throw ValidationException::withMessages([
                    'bar_code' => ['The bar code has already been taken.'],
                ]);
            }
            $product = Product::create([
                'bar_code' => $data['bar_code'],
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => true,
                'price' => $data['price'],
                'stock' => $data['stock'],
            ]);
            return $product;
        });
    }

    public function view(int $product_id): Product
    {
        $product = Product::find($product_id);
        if (!$product) throw new BadRequestException('Product not found.');
        return $product;
    }

    public function list(int $per_page, string $order): LengthAwarePaginator
    {
        return Product::query()
            ->orderBy('id', $order)
            ->paginate($per_page);
    }

    public function update(int $product_id, $data): void
    {
        DB::transaction(function () use ($product_id, $data) {
            $product = Product::find($product_id);
            if (!$product) throw new BadRequestException('Product not found.');
            $product->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'is_active' => $data['is_active'],
                'price' => $data['price'],
                'stock' => $data['stock']
            ]);
        });
    }

    public function delete(int $product_id): void
    {
        DB::transaction(function () use ($product_id) {
            $product = Product::find($product_id);
            if (!$product) throw new BadRequestException('Product not found.');
            $product->delete();
        });
    }
}
