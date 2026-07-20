<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductsRequest $request)
    {
        $products = Product::with('category')
            ->search($request['search'])
            ->latest()
            ->paginate($request['limit'] ?? 10);

        return ApiResponse::succes(
            new PaginatedResource($products, ProductResource::class),
            'Products'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        $product->load('category');

        return ApiResponse::succes(
            new ProductResource($product),
            'Product Created Successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return ApiResponse::error('Product Not Found', Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::succes(
            new ProductResource($product),
            'Product'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error('Product Not Found', Response::HTTP_NOT_FOUND);
        }

        $product->update($request->validated());

        $product->load('category');

        return ApiResponse::succes(
            new ProductResource($product),
            'Product Updated Successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error('Product Not Found', Response::HTTP_NOT_FOUND);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return ApiResponse::succes(
            null,
            'Product Deleted Successfully',
            Response::HTTP_OK
        );
    }
}
